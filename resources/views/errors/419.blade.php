@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Your Session Has Evaporated'))
@section('description', __('Your session has expired like water under the sun. Please refresh and try again to continue your farm management.'))
@section('icon', '<div class="fish-icon">💨</div>')
