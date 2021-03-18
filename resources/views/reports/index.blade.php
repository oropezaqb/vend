@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Reports)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Accounting</h6>
                            <div style="display:inline-block;"><form method="POST" action="/reports/comprehensive_income">
                                @csrf
                                <button class="btn btn-link" type="submit">Generate</button>
                            </form></div>
                            <div style="display:inline-block;">&nbsp;&nbsp;<span>Statement of Comprehensive Income</span></div><br>
                            <div style="display:inline-block;"><form method="GET" action="/reports/financial_position">
                                @csrf
                                <button class="btn btn-link" type="submit">Generate</button>
                            </form></div>
                            <div style="display:inline-block;">&nbsp;&nbsp;<span>Statement of Financial Position</span></div><br>
                            <div style="display:inline-block;"><form method="GET" action="/reports/changes_in_equity">
                                @csrf
                                <button class="btn btn-link" type="submit">Generate</button>
                            </form></div>
                            <div style="display:inline-block;">&nbsp;&nbsp;<span>Statement of Changes in Owner&#39;s Equity</span></div><br>
                            <div style="display:inline-block;"><form method="GET" action="/reports/cash_flows">
                                @csrf
                                <button class="btn btn-link" type="submit">Generate</button>
                            </form></div>
                            <div style="display:inline-block;">&nbsp;&nbsp;<span>Statement of Cash Flows</span></div><br>
                            <div style="display:inline-block;"><form method="POST" action="/reports/trial_balance">
                                @csrf
                                <button class="btn btn-link" type="submit">Generate</button>
                            </form></div>
                            <div style="display:inline-block;">&nbsp;&nbsp;<span>Trial Balance</span></div><br><br>
                        <h6 class="font-weight-bold">Others</h6>
                        @forelse ($queries as $query)
                            <div id="query">
                                <div style="display:inline-block;"><form method="POST" action="/reports/{{ $query->id }}/screen">
                                    @csrf
                                    <button class="btn btn-link" type="submit">Screen</button>
                                </form></div>
                                <div style="display:inline-block;"><form method="POST" action="/reports/{{ $query->id }}/pdf">
                                    @csrf
                                    <button class="btn btn-link" type="submit">PDF</button>
                                </form></div>
                                <div style="display:inline-block;"><form method="POST" action="/reports/{{ $query->id }}/csv">
                                    @csrf
                                    <button class="btn btn-link" type="submit">CSV</button>
                                </form></div>
                                <div style="display:inline-block;">&nbsp;&nbsp;{{ $query->title }}</div>
                            </div>
                        @empty
                            <p>No reports available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
