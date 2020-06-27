@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Notifications</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        @forelse($notifications as $type => $notification)
                            <div style="display:inline-block;">
                                <a class="dropdown-item d-flex align-items-center" href="{{ $notification->data['link'] }}">
                                    <div class="mr-3">
                                      <div class="icon-circle bg-primary">
                                        <i class="{{ $notification->data['class'] }}"></i>
                                      </div>
                                    </div>
                                    <div>
                                      <div class="small text-gray-500">{{ $notification->created_at }}</div>
                                      {{ $notification->data['message'] }}
                                    </div>
                                </a>
                            </div>
                            <div style="display:inline-block;" class="float-right">
                                <form method="POST" action="/notifications/{{ $notification->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link" type="submit">Delete</button>
                                </form>
                            </div>
                            <br>
                        @empty
                            <p>There are no new notifications.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
