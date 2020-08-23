@extends('layouts.app2')

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">{{ $thread->subject }}</div>

            <div class="card-body">

        @each('messenger.partials.messages', $thread->messages, 'message')

        @include('messenger.partials.form-message')

            </div>
        </div>
    </div>
@stop
