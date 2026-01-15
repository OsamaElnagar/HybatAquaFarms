@extends('errors::minimal')

@section('title', __('حدث خطأ في الخادم'))
@section('code', '500')
@section('message', __('حدث خطأ غير متوقع'))
@section('description', __('حدث خلل مفاجئ أثناء معالجة طلبك. نعمل على حل المشكلة، يرجى المحاولة مرة أخرى بعد قليل.'))

@section('icon')
    <div class="fish-icon" aria-hidden="true">🐠</div>
@endsection
