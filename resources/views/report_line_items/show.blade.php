@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Report Line Item Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <div id="content">
                            <div id="name">
                                <p>Report Name: {{ $reportLineItem->report }}</p>
                                <p>Section Name: {{ !empty($reportLineItem->section) ? $reportLineItem->section : 'Not Applicable' }}</p>
                                <p>Line Item: {{ $reportLineItem->line_item }}</p>
                            </div>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/report_line_items/{{ $reportLineItem->id }}/edit';">Edit</button></div>
                            <div style="display:inline-block;"><form method="POST" action="/report_line_items/{{ $reportLineItem->id }}">
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
