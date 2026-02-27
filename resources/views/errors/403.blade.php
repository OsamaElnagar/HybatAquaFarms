@extends('errors::minimal')

@section('title', __('صلاحيات غير كافية'))
@section('code', '403')
@section('message', __('صلاحيات غير كافية'))
@section('description', $exception->getMessage() ?: __('عذراً، لا تملك الصلاحيات الكافية للوصول إلى هذه الصفحة.'))