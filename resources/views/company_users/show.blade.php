@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Company User Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <div id="content">
                            <p>User Name: {!! $user->name !!}</p>
                            <div id="roles">
                                <p style="margin-top: 0; margin-bottom: 1; margin-left: 0;">Roles:</p>
                                @forelse ($roles as $role)
                                    <p style="margin-top: 0; margin-bottom: 1; margin-left: 0;">&nbsp;&nbsp;&nbsp;&nbsp;{{ $role->name }}</p>
                                @empty
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;None.</p>
                                @endforelse
                            </div>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/company_users/{{ $user->id }}/edit';">Edit</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
