@extends('errors::minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __('Farm Under Maintenance'))
@section('description', __('We\'re currently performing routine maintenance on our systems. Like cleaning the tanks, this ensures everything runs smoothly. We\'ll be back shortly!'))
@section('icon', '<div class="fish-icon">🔧</div>')
