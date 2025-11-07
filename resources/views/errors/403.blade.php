@extends('errors::minimal')

@section('title', __('Access Forbidden'))
@section('code', '403')
@section('message', __('This Pond is Protected'))
@section('description', $exception->getMessage() ?: __('You don\'t have permission to access this area. Some waters are reserved for authorized personnel only.'))
@section('icon', '<div class="fish-icon">🚫</div>')
