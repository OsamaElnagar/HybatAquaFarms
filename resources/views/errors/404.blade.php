@extends('errors::minimal')

@section('title', __('الصفحة غير موجودة'))
@section('code', '404')
@section('message', __('السمكة تاهت عن مسارها'))
@section('description', __('لم نتمكن من العثور على الصفحة التي تبحث عنها. ربما تم نقلها أو لم تعد متاحة.'))

@section('icon')
    <div class="fish-icon" aria-hidden="true">🐟</div>
@endsection
