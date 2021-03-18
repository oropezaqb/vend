@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">{{ auth()->user()->currentCompany->company->name }}</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <h3 style='text-align:center;'>{{ auth()->user()->currentCompany->company->name }}</h3>
                            <h5 style='font-weight: bold;text-align:center;'>{{ $query->title }}</h5>
                            <p style='text-align:center;'>{{ $query->date }}</p>
                            <br>
                            <div class="run">
                                <table border=0 cellpadding=5 cellspacing=0 align=center style='border-collapse: collapse; border: 0px solid rgb(192, 192, 192);'>
                                    <tr>
                                        <th style='font-weight: bold; text-align: left;'>
                                        @foreach($equities as $equity)
                                            <th style='text-align: right;'>{{ \App\Lineitem::find($equity->id)->name }}
                                        @endforeach
                                        @foreach($appropriatedREs as $appropriatedRE)
                                            <th style='text-align: right;'>{{ \App\Lineitem::find($appropriatedRE->id)->name }}
                                        @endforeach
                                        <th style='text-align: right;'>Unappropriated retained earnings
                                        <th style='text-align: right;'>Total Equity
                                    <tr>
                                        <td style='text-align: left; width: 240px;'>As at {{ date_format($begDate, 'M d, Y') }}
                                        @foreach($amounts['beg'] as $begAmount)
                                            <td style='text-align: right; vertical-align: bottom; border-bottom-style: solid; border-bottom-width:1px;'>{{ $begAmount }}
                                        @endforeach
                                        <td style='text-align: right; vertical-align: bottom; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['beg_retained_earnings'] }}
                                        <td style='text-align: right; vertical-align: bottom; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['beg_total_equity'] }}
                                    <tr>
                                        <td style='text-align: left;'>Net income
                                        @foreach($equities as $equity)
                                            <td style='text-align: right; vertical-align: bottom;'>{{ number_format(0, 2) }}
                                        @endforeach
                                        @foreach($appropriatedREs as $appropriatedRE)
                                            <td style='text-align: right; vertical-align: bottom;'>{{ number_format(0, 2) }}
                                        @endforeach
                                        <td style='text-align: right; vertical-align: bottom;'>{{ $amounts['net_income'] }}
                                        <td style='text-align: right; vertical-align: bottom;'>{{ $amounts['net_income'] }}
                                    <tr>
                                        <td style='text-align: left;'>Other comprehensive income
                                        @foreach($amounts['OCI'] as $OCIAmount)
                                            <td style='text-align: right; vertical-align: bottom; border-bottom-style: solid; border-bottom-width:1px;'>{{ $OCIAmount }}
                                        @endforeach
                                        <td style='text-align: right; vertical-align: bottom; border-bottom-style: solid; border-bottom-width:1px;'>{{ number_format(0, 2) }}
                                        <td style='text-align: right; vertical-align: bottom; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['total_OCI'] }}
                                    <tr>
                                        <td style='text-align: left;'>Total comprehensive income
                                        @foreach($amounts['TCI'] as $TCIAmount)
                                            <td style='text-align: right; vertical-align: bottom;'>{{ $TCIAmount }}
                                        @endforeach
                                        @foreach($appropriatedREs as $appropriatedRE)
                                            <td style='text-align: right; vertical-align: bottom;'>{{ number_format(0, 2) }}
                                        @endforeach
                                        <td style='text-align: right; vertical-align: bottom;'>{{ $amounts['net_income'] }}
                                        <td style='text-align: right; vertical-align: bottom;'>{{ $amounts['total_TCI'] }}
                                    @foreach($reportLineItems as $reportLineItem)
                                        <tr>
                                            <td style='text-align: left;'>{{ \App\ReportLineitem::find($reportLineItem->id)->line_item }}
                                            @foreach($equities as $equity)
                                                <td style='text-align: right; vertical-align: bottom;'>{{ $amounts[$reportLineItem->id][$equity->id] }}
                                            @endforeach
                                            @foreach($appropriatedREs as $appropriatedRE)
                                                <td style='text-align: right; vertical-align: bottom;'>{{ $amounts[$reportLineItem->id][$appropriatedRE->id] }}
                                            @endforeach
                                            <td style='text-align: right; vertical-align: bottom;'>{{ $amounts['retained_earnings'][$reportLineItem->id] }}
                                            <td style='text-align: right; vertical-align: bottom;'>{{ $amounts['line_item_total'][$reportLineItem->id] }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left; width: 240px;'>As at {{ date_format($endDate, 'M d, Y') }}
                                        @foreach($equities as $equity)
                                            <td style='text-align: right; vertical-align: bottom; border-top-style: solid; border-top-width:1px; border-bottom-style: double;'>{{ $equity->debit }}
                                        @endforeach
                                        @foreach($appropriatedREs as $appropriatedRE)
                                            <td style='text-align: right; vertical-align: bottom; border-top-style: solid; border-top-width:1px; border-bottom-style: double;'>{{ $appropriatedRE->debit }}
                                        @endforeach
                                        <td style='text-align: right; vertical-align: bottom; border-top-style: solid; border-top-width:1px; border-bottom-style: double;'>{{ $amounts['end_retained_earnings'] }}
                                        <td style='text-align: right; vertical-align: bottom; border-top-style: solid; border-top-width:1px; border-bottom-style: double;'>{{ $amounts['end_total_equity'] }}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
