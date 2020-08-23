@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Run Report)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <p>Click below to access the report:</p>
                            <p><a href='{{ $url }}'>{{ $url }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
