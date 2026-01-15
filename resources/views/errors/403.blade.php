@extends('errors::minimal')

@section('title', __('صلاحيات غير كافية'))
@section('code', '403')
@section('message', __('لا يمكنك الوصول إلى هذا الحوض'))
@section('description', $exception->getMessage() ?: __('يبدو أن صلاحيات حسابك لا تسمح لك بالوصول إلى هذه المنطقة في المزرعة.'))

@section('icon')
    <div class="fish-icon" aria-hidden="true">🚫</div>
@endsection
