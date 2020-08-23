@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Company Role Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <div id="content">
                            <p>Role Name: {!! $role->name !!}</p>
                            <div id="abilities">
                                <p style="margin-top: 0; margin-bottom: 1; margin-left: 0;">Abilities:</p>
                                @forelse ($abilities as $ability)
                                    <p style="margin-top: 0; margin-bottom: 1; margin-left: 0;">&nbsp;&nbsp;&nbsp;&nbsp;{{ $ability->name }}</p>
                                @empty
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;None.</p>
                                @endforelse
                            </div>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/roles/{{ $role->id }}/edit';">Edit</button></div>
                            <div style="display:inline-block;"><form method="POST" action="/roles/{{ $role->id }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
