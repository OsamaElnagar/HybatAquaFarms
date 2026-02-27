@extends('errors::minimal')

@section('title', __('حدث خطأ في الخادم'))
@section('code', '500')
@section('message', __('حدث خطأ في الخادم'))
@section('description', __('عذراً، حدث خطأ غير متوقع في الخادم. نحن نعمل على حل المشكلة، يرجى المحاولة لاحقاً.'))