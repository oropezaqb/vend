@extends('layouts.app2')

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Messages</div>

            <div class="card-body">
                @include('messenger.partials.flash')

                @each('messenger.partials.thread', $threads, 'thread', 'messenger.partials.no-threads')
            </div>
        </div>
    </div>
@stop
