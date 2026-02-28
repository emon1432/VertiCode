@extends('errors.layout')

@section('error_code', '429')
@section('error_title', 'Too many requests')
@section('error_message', 'You are making requests too quickly. Please wait a moment before trying again.')
