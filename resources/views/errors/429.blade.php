@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Slow Down the Feeding!'))
@section('description', __('You\'re making requests too quickly! Like overfeeding your fish, moderation is key. Please wait a moment before trying again.'))
@section('icon', '<div class="fish-icon">⏱️</div>')
