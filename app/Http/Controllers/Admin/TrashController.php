<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\View\Components\ActionBy;
use App\View\Components\Actions;
use App\View\Components\ItemInfo;
use App\View\Components\UserInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    private $database;
    private $tables;
    private $skipTables;

    public function __construct()
    {
        $this->database = env('DB_DATABASE');
        $this->tables = DB::select("SHOW TABLES");
        $this->skipTables = [
            'migrations',
            'activity_logs',
            'failed_jobs',
            'personal_access_tokens',
            'cache',
            'jobs',
            'sessions',
            'trash'
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data());
        }

        return view('admin.pages.trash.index');
    }

    public function restore(string $table, string $id)
    {
        try {
            $modelClass = "App\\Models\\" . Str::studly(Str::singular($table));
            if (!class_exists($modelClass)) {
                throw new \Exception("Model class for table {$table} does not exist.");
            }
            $record = $modelClass::onlyTrashed()->findOrFail($id);
            $record->restore();

            return response()->json([
                'status' => 200,
                'message' => __('Record restored successfully'),
                'redirect' => route('trash.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function destroy(string $table, string $id)
    {
        try {
            if (!Schema::hasTable($table)) {
                throw new \Exception("Table {$table} does not exist.");
            }

            $modelClass = "App\\Models\\" . Str::studly(Str::singular($table));

            if (!class_exists($modelClass)) {
                throw new \Exception("Model class for table {$table} does not exist.");
            }

            $record = $modelClass::onlyTrashed()->findOrFail($id);
            $record->forceDelete();

            return response()->json([
                'status' => 200,
                'message' => __('Record permanently deleted successfully'),
                'redirect' => route('trash.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    private function data()
    {
        $items = [];

        foreach ($this->tables as $table) {
            $tableName = $table->{"Tables_in_{$this->database}"};

            if (in_array($tableName, $this->skipTables)) {
                continue;
            }

            if (Schema::hasColumn($tableName, 'deleted_at')) {
                $modelClass = "App\\Models\\" . Str::studly(Str::singular($tableName));
                if (class_exists($modelClass)) {
                    if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($modelClass))) {
                        $deletedRecords = $modelClass::onlyTrashed()->get();
                        if ($deletedRecords->isNotEmpty()) {
                            foreach ($deletedRecords as $record) {
                                $items[] = [
                                    'table'  => $tableName,
                                    'model'  => class_basename($modelClass),
                                    'record' => $record,
                                    'deleted_at' => $record->deleted_at,
                                    'deleted_by' => $record->deleted_by,
                                ];
                            }
                        }
                    }
                }
            }
            usort($items, function ($a, $b) {
                return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
            });
        }
        return collect($items)->map(function ($trash) {
            $trash = (object) $trash;
            $trash->actions = (new Actions([
                'model' => $trash,
                'resource' => 'trash',
                'buttons' => [
                    'custom' => [
                        'Restore' => [
                            'route' => 'javascript:void(0);',
                            'route_name' => 'trash.restore',
                            'icon' => 'restore',
                            'label' => 'Restore',
                            'class' => 'restore-record',
                            'form' => [
                                'method' => 'POST',
                                'action' => route('trash.restore', [$trash->table, $trash->record->id]),
                            ],
                        ],
                        'Delete Permanently' => [
                            'route' => 'javascript:void(0);',
                            'route_name' => 'trash.destroy',
                            'icon' => 'trash-x',
                            'label' => 'Delete Permanently',
                            'class' => 'permanently-delete-record',
                            'form' => [
                                'method' => 'DELETE',
                                'action' => route('trash.destroy', [$trash->table, $trash->record->id]),
                            ],
                        ],
                    ],
                ],
                'basic' => [
                    'view' => false,
                    'edit' => false,
                    'delete' => false,
                ],
            ]))->render()->render();
            if ($trash->model == 'User') {
                $trash->info = (new UserInfo($trash->record))->render()->render();
            } else {
                $trash->info = (new ItemInfo($trash->record))->render()->render();
            }
            $trash->model = $trash->model;
            $trash->deleted_by = (new ActionBy($trash->record->deletedBy))->render()->render();
            $trash->deleted_at = $trash->record->deleted_at->diffForHumans();
            return $trash;
        })->toArray();
    }
}
