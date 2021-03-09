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
                        <h6 class="font-weight-bold">Statement of Changes in Equity</h6>
                        <br>
                        <form method="POST" action="/reports/run_changes_in_equity">
                            @csrf
                            <div class="form-group custom-control-inline">
                                <label for="beg_date">Beginning&nbsp;date:&nbsp;</label>&nbsp;
                                <input class="form-control" type="date" name="beg_date" required value="{!! old('beg_date') !!}">
                            </div>
                            <div class="form-group custom-control-inline">
                                <label for="end_date">Ending&nbsp;date:&nbsp;</label>&nbsp;
                                <input class="form-control" type="date" name="end_date" required value="{!! old('end_date') !!}">
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