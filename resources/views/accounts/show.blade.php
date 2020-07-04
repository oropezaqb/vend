@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Account Title Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <div id="content">
                            <div id="name">
                                <p>Account Number: {{ $account->number }}</p>
                                <p>Account Title: {{ $account->title }}</p>
                                <p>Account Type: {{ $account->type }}</p>
                                <p>With Subsidiary Ledger: {{ $account->subsidiary_ledger ? 'Yes' : 'No' }}</p>
                            </div>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/accounts/{{ $account->id }}/edit';">Edit</button></div>
                            <div style="display:inline-block;"><form method="POST" action="/accounts/{{ $account->id }}">
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
