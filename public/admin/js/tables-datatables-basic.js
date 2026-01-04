let fv, offCanvasEl;
document.addEventListener("DOMContentLoaded", function (e) {
    (t = document.getElementById("form-add-new-record")),
        setTimeout(() => {
            let e = document.querySelector(".create-new"),
                t = document.querySelector("#add-new-record");
            e &&
                e.addEventListener("click", function () {
                    (offCanvasEl = new bootstrap.Offcanvas(t)),
                        (t.querySelector(".dt-full-name").value = ""),
                        (t.querySelector(".dt-post").value = ""),
                        (t.querySelector(".dt-email").value = ""),
                        (t.querySelector(".dt-date").value = ""),
                        (t.querySelector(".dt-salary").value = ""),
                        offCanvasEl.show();
                });
        }, 200),
        (fv = FormValidation.formValidation(t, {
            fields: {
                basicFullname: {
                    validators: {
                        notEmpty: {
                            message: "The name is required",
                        },
                    },
                },
                basicPost: {
                    validators: {
                        notEmpty: {
                            message: "Post field is required",
                        },
                    },
                },
                basicEmail: {
                    validators: {
                        notEmpty: {
                            message: "The Email is required",
                        },
                        emailAddress: {
                            message: "The value is not a valid email address",
                        },
                    },
                },
                basicDate: {
                    validators: {
                        notEmpty: {
                            message: "Joining Date is required",
                        },
                        date: {
                            format: "MM/DD/YYYY",
                            message: "The value is not a valid date",
                        },
                    },
                },
                basicSalary: {
                    validators: {
                        notEmpty: {
                            message: "Basic Salary is required",
                        },
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: "",
                    rowSelector: ".form-control-validation",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
            init: (e) => {
                e.on("plugins.message.placed", function (e) {
                    e.element.parentElement.classList.contains("input-group") &&
                        e.element.parentElement.insertAdjacentElement(
                            "afterend",
                            e.messageElement
                        );
                });
            },
        })),
        (t = document.querySelector('[name="basicDate"]')) &&
            t.flatpickr({
                enableTime: !1,
                monthSelectorType: "static",
                static: !0,
                dateFormat: "m/d/Y",
                onChange: function () {
                    fv.revalidateField("basicDate");
                },
            });
    var n,
        t = document.querySelector(".datatables-basic");
    let l;
    t &&
        ((s = document.createElement("h5")).classList.add(
            "card-title",
            "mb-0",
            "text-md-start",
            "text-center",
            "pb-md-0",
            "pb-6"
        ),
        (s.innerHTML = "DataTable with Buttons"),
        (l = new DataTable(t, {
            ajax: assetsPath + "json/table-datatable.json",
            columns: [
                {
                    data: "id",
                },
                {
                    data: "id",
                    orderable: !1,
                    render: DataTable.render.select(),
                },
                {
                    data: "id",
                },
                {
                    data: "full_name",
                },
                {
                    data: "email",
                },
                {
                    data: "start_date",
                },
                {
                    data: "salary",
                },
                {
                    data: "status",
                },
                {
                    data: "id",
                },
            ],
            columnDefs: [
                {
                    className: "control",
                    orderable: !1,
                    searchable: !1,
                    responsivePriority: 2,
                    targets: 0,
                    render: function (e, t, a, s) {
                        return "";
                    },
                },
                {
                    targets: 1,
                    orderable: !1,
                    searchable: !1,
                    responsivePriority: 3,
                    checkboxes: !0,
                    checkboxes: {
                        selectAllRender:
                            '<input type="checkbox" class="form-check-input">',
                    },
                    render: function () {
                        return '<input type="checkbox" class="dt-checkboxes form-check-input">';
                    },
                },
                {
                    targets: 2,
                    searchable: !1,
                    visible: !1,
                },
                {
                    targets: 3,
                    responsivePriority: 4,
                    render: function (e, t, a, s) {
                        var r = a.avatar,
                            n = a.full_name,
                            a = a.post;
                        let l;
                        if (r)
                            l = `<img src="${assetsPath}img/avatars/${r}" alt="Avatar" class="rounded-circle">`;
                        else {
                            r = [
                                "success",
                                "danger",
                                "warning",
                                "info",
                                "dark",
                                "primary",
                                "secondary",
                            ][Math.floor(6 * Math.random())];
                            let e = n.match(/\b\w/g) || [];
                            (e = (
                                (e.shift() || "") + (e.pop() || "")
                            ).toUpperCase()),
                                (l = `<span class="avatar-initial rounded-circle bg-label-${r}">${e}</span>`);
                        }
                        return `
              <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                  <div class="avatar me-2">
                    ${l}
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span class="emp_name text-truncate text-heading fw-medium">${n}</span>
                  <small class="emp_post text-truncate">${a}</small>
                </div>
              </div>
            `;
                    },
                },
                {
                    responsivePriority: 1,
                    targets: 4,
                },
                {
                    targets: -2,
                    render: function (e, t, a, s) {
                        var a = a.status,
                            r = {
                                1: {
                                    title: "Current",
                                    class: "bg-label-primary",
                                },
                                2: {
                                    title: "Professional",
                                    class: "bg-label-success",
                                },
                                3: {
                                    title: "Rejected",
                                    class: "bg-label-danger",
                                },
                                4: {
                                    title: "Resigned",
                                    class: "bg-label-warning",
                                },
                                5: {
                                    title: "Applied",
                                    class: "bg-label-info",
                                },
                            };
                        return void 0 === r[a]
                            ? e
                            : `
              <span class="badge ${r[a].class}">
                ${r[a].title}
              </span>
            `;
                    },
                },
                {
                    targets: -1,
                    title: "Actions",
                    orderable: !1,
                    searchable: !1,
                    render: function (e, t, a, s) {
                        return '<div class="d-inline-block"><a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base ti tabler-dots-vertical"></i></a><ul class="dropdown-menu dropdown-menu-end m-0"><li><a href="javascript:;" class="dropdown-item">Details</a></li><li><a href="javascript:;" class="dropdown-item">Archive</a></li><div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a></li></ul></div><a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill waves-effect item-edit"><i class="icon-base ti tabler-pencil"></i></a>';
                    },
                },
            ],
            select: {
                style: "multi",
                selector: "td:nth-child(2)",
            },
            order: [[2, "desc"]],
            layout: {
                top2Start: {
                    rowClass:
                        "row card-header flex-column flex-md-row border-bottom mx-0 px-3",
                    features: [s],
                },
                top2End: {
                    features: [
                        {
                            buttons: [
                                {
                                    extend: "collection",
                                    className:
                                        "btn btn-label-primary dropdown-toggle me-4",
                                    text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-upload icon-xs me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                                    buttons: [
                                        {
                                            extend: "print",
                                            text: '<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>',
                                            className: "dropdown-item",
                                            exportOptions: {
                                                columns: [3, 4, 5, 6, 7],
                                                format: {
                                                    body: function (e, t, a) {
                                                        if (
                                                            e.length <= 0 ||
                                                            !(
                                                                -1 <
                                                                e.indexOf("<")
                                                            )
                                                        )
                                                            return e;
                                                        {
                                                            e =
                                                                new DOMParser().parseFromString(
                                                                    e,
                                                                    "text/html"
                                                                );
                                                            let t = "";
                                                            var s =
                                                                e.querySelectorAll(
                                                                    ".user-name"
                                                                );
                                                            return (
                                                                0 < s.length
                                                                    ? s.forEach(
                                                                          (
                                                                              e
                                                                          ) => {
                                                                              e =
                                                                                  e.querySelector(
                                                                                      ".fw-medium"
                                                                                  )
                                                                                      ?.textContent ||
                                                                                  e.querySelector(
                                                                                      ".d-block"
                                                                                  )
                                                                                      ?.textContent ||
                                                                                  e.textContent;
                                                                              t +=
                                                                                  e.trim() +
                                                                                  " ";
                                                                          }
                                                                      )
                                                                    : (t =
                                                                          e.body
                                                                              .textContent ||
                                                                          e.body
                                                                              .innerText),
                                                                t.trim()
                                                            );
                                                        }
                                                    },
                                                },
                                            },
                                            customize: function (e) {
                                                (e.document.body.style.color =
                                                    config.colors.headingColor),
                                                    (e.document.body.style.borderColor =
                                                        config.colors.borderColor),
                                                    (e.document.body.style.backgroundColor =
                                                        config.colors.bodyBg);
                                                e =
                                                    e.document.body.querySelector(
                                                        "table"
                                                    );
                                                e.classList.add("compact"),
                                                    (e.style.color = "inherit"),
                                                    (e.style.borderColor =
                                                        "inherit"),
                                                    (e.style.backgroundColor =
                                                        "inherit");
                                            },
                                        },
                                        {
                                            extend: "csv",
                                            text: '<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Csv</span>',
                                            className: "dropdown-item",
                                            exportOptions: {
                                                columns: [3, 4, 5, 6, 7],
                                                format: {
                                                    body: function (e, t, a) {
                                                        if (e.length <= 0)
                                                            return e;
                                                        e =
                                                            new DOMParser().parseFromString(
                                                                e,
                                                                "text/html"
                                                            );
                                                        let s = "";
                                                        var r =
                                                            e.querySelectorAll(
                                                                ".user-name"
                                                            );
                                                        return (
                                                            0 < r.length
                                                                ? r.forEach(
                                                                      (e) => {
                                                                          e =
                                                                              e.querySelector(
                                                                                  ".fw-medium"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.querySelector(
                                                                                  ".d-block"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.textContent;
                                                                          s +=
                                                                              e.trim() +
                                                                              " ";
                                                                      }
                                                                  )
                                                                : (s =
                                                                      e.body
                                                                          .textContent ||
                                                                      e.body
                                                                          .innerText),
                                                            s.trim()
                                                        );
                                                    },
                                                },
                                            },
                                        },
                                        {
                                            extend: "excel",
                                            text: '<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-spreadsheet me-1"></i>Excel</span>',
                                            className: "dropdown-item",
                                            exportOptions: {
                                                columns: [3, 4, 5, 6, 7],
                                                format: {
                                                    body: function (e, t, a) {
                                                        if (e.length <= 0)
                                                            return e;
                                                        e =
                                                            new DOMParser().parseFromString(
                                                                e,
                                                                "text/html"
                                                            );
                                                        let s = "";
                                                        var r =
                                                            e.querySelectorAll(
                                                                ".user-name"
                                                            );
                                                        return (
                                                            0 < r.length
                                                                ? r.forEach(
                                                                      (e) => {
                                                                          e =
                                                                              e.querySelector(
                                                                                  ".fw-medium"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.querySelector(
                                                                                  ".d-block"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.textContent;
                                                                          s +=
                                                                              e.trim() +
                                                                              " ";
                                                                      }
                                                                  )
                                                                : (s =
                                                                      e.body
                                                                          .textContent ||
                                                                      e.body
                                                                          .innerText),
                                                            s.trim()
                                                        );
                                                    },
                                                },
                                            },
                                        },
                                        {
                                            extend: "pdf",
                                            text: '<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-description me-1"></i>Pdf</span>',
                                            className: "dropdown-item",
                                            exportOptions: {
                                                columns: [3, 4, 5, 6, 7],
                                                format: {
                                                    body: function (e, t, a) {
                                                        if (e.length <= 0)
                                                            return e;
                                                        e =
                                                            new DOMParser().parseFromString(
                                                                e,
                                                                "text/html"
                                                            );
                                                        let s = "";
                                                        var r =
                                                            e.querySelectorAll(
                                                                ".user-name"
                                                            );
                                                        return (
                                                            0 < r.length
                                                                ? r.forEach(
                                                                      (e) => {
                                                                          e =
                                                                              e.querySelector(
                                                                                  ".fw-medium"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.querySelector(
                                                                                  ".d-block"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.textContent;
                                                                          s +=
                                                                              e.trim() +
                                                                              " ";
                                                                      }
                                                                  )
                                                                : (s =
                                                                      e.body
                                                                          .textContent ||
                                                                      e.body
                                                                          .innerText),
                                                            s.trim()
                                                        );
                                                    },
                                                },
                                            },
                                        },
                                        {
                                            extend: "copy",
                                            text: '<i class="icon-base ti tabler-copy me-1"></i>Copy',
                                            className: "dropdown-item",
                                            exportOptions: {
                                                columns: [3, 4, 5, 6, 7],
                                                format: {
                                                    body: function (e, t, a) {
                                                        if (e.length <= 0)
                                                            return e;
                                                        e =
                                                            new DOMParser().parseFromString(
                                                                e,
                                                                "text/html"
                                                            );
                                                        let s = "";
                                                        var r =
                                                            e.querySelectorAll(
                                                                ".user-name"
                                                            );
                                                        return (
                                                            0 < r.length
                                                                ? r.forEach(
                                                                      (e) => {
                                                                          e =
                                                                              e.querySelector(
                                                                                  ".fw-medium"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.querySelector(
                                                                                  ".d-block"
                                                                              )
                                                                                  ?.textContent ||
                                                                              e.textContent;
                                                                          s +=
                                                                              e.trim() +
                                                                              " ";
                                                                      }
                                                                  )
                                                                : (s =
                                                                      e.body
                                                                          .textContent ||
                                                                      e.body
                                                                          .innerText),
                                                            s.trim()
                                                        );
                                                    },
                                                },
                                            },
                                        },
                                    ],
                                },
                                {
                                    text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-plus icon-sm"></i> <span class="d-none d-sm-inline-block">Add New Record</span></span>',
                                    className: "create-new btn btn-primary",
                                },
                            ],
                        },
                    ],
                },
                topStart: {
                    rowClass:
                        "row mx-0 px-3 my-0 justify-content-between border-bottom",
                    features: [
                        {
                            pageLength: {
                                menu: [10, 25, 50, 100],
                                text: "Show_MENU_entries",
                            },
                        },
                    ],
                },
                topEnd: {
                    search: {
                        placeholder: "",
                    },
                },
                bottomStart: {
                    rowClass: "row mx-3 justify-content-between",
                    features: ["info"],
                },
                bottomEnd: "paging",
            },
            language: {
                paginate: {
                    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
                    previous:
                        '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
                    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
                    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>',
                },
            },
            responsive: {
                details: {
                    display: DataTable.Responsive.display.modal({
                        header: function (e) {
                            return "Details of " + e.data().full_name;
                        },
                    }),
                    type: "column",
                    renderer: function (e, t, a) {
                        var s,
                            r,
                            n,
                            a = a
                                .map(function (e) {
                                    return "" !== e.title
                                        ? `<tr data-dt-row="${e.rowIndex}" data-dt-column="${e.columnIndex}">
                      <td>${e.title}:</td>
                      <td>${e.data}</td>
                    </tr>`
                                        : "";
                                })
                                .join("");
                        return (
                            !!a &&
                            ((s = document.createElement("div")).classList.add(
                                "table-responsive"
                            ),
                            (r = document.createElement("table")),
                            s.appendChild(r),
                            r.classList.add("table"),
                            r.classList.add("datatables-basic"),
                            ((n = document.createElement("tbody")).innerHTML =
                                a),
                            r.appendChild(n),
                            s)
                        );
                    },
                },
            },
        })),
        (n = 101),
        fv.on("core.form.valid", function () {
            var e = document.querySelector(
                    ".add-new-record .dt-full-name"
                ).value,
                t = document.querySelector(".add-new-record .dt-post").value,
                a = document.querySelector(".add-new-record .dt-email").value,
                s = document.querySelector(".add-new-record .dt-date").value,
                r = document.querySelector(".add-new-record .dt-salary").value;
            "" != e &&
                (l.row
                    .add({
                        id: n,
                        full_name: e,
                        post: t,
                        email: a,
                        start_date: s,
                        salary: "$" + r,
                        status: 5,
                    })
                    .draw(),
                n++,
                offCanvasEl.hide());
        }),
        document.addEventListener("click", function (e) {
            e.target.classList.contains("delete-record") &&
                (l.row(e.target.closest("tr")).remove().draw(),
                (e = document.querySelector(".dtr-bs-modal"))) &&
                e.classList.contains("show") &&
                bootstrap.Modal.getInstance(e)?.hide();
        }));
});
