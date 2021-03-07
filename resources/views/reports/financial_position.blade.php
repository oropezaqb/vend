@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">{{ \Auth::user()->currentCompany->company->name }}</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <h6 class="font-weight-bold">Statement of Financial Position</h6>
                        <br>
                        <form method="POST" action="/reports/run_financial_position">
                            @csrf
                            <div class="form-group custom-control-inline">
                                <label for="date">As&nbsp;of:&nbsp;</label>&nbsp;
                                <input class="form-control" type="date" name="date" required value="{!! old('date') !!}">
                            </div>
                        <br>
                        <button class="btn btn-primary" type="submit">Run report</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection