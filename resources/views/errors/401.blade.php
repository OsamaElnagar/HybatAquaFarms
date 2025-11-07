@extends('errors::minimal')

@section('title', __('Unauthorized Access'))
@section('code', '401')
@section('message', __('This Pond Requires Credentials'))
@section('description', __('You need proper authorization to access this area. Please log in to dive into these waters.'))
@section('icon', '<div class="fish-icon">🔒</div>')
