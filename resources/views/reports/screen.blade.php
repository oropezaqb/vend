@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Run Report)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <p style='font-weight: bold;'>{{ $query->title }}</p>
                            <div class="run">
                                @if (!is_null($stmt))
                                    <table border=1 cellpadding=5 cellspacing=0 style='border-collapse: collapse; border: 1px solid rgb(192, 192, 192);'>
                                        <tr>
                                        @foreach ($headings as $h)
                                            <th>{{ htmlspecialchars($h, ENT_QUOTES, 'UTF-8') }}
                                        @endforeach
                                        @while ($row = $stmt->fetch())
                                            <tr>
                                            @foreach ($row as $v)
                                                <td>{{ htmlspecialchars($v, ENT_QUOTES, 'UTF-8') }}
                                            @endforeach
                                        @endwhile
                                    </table>
                                @else
                                    <p>The query returned an empty set.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
