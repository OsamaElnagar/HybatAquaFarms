@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Something\'s Fishy Here'))
@section('description', __('We encountered an unexpected issue with our system. Our team is working to get things back to normal. Please try again in a few moments.'))
@section('icon', '<div class="fish-icon">🐠</div>')
