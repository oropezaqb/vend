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
                                        <td style='font-weight: bold; text-align: left;'>CASH FLOWS FROM OPERATING ACTIVITIES
                                        <td>
                                    @foreach($operatingCashFlows as $operatingCashFlow)
                                        <tr>
                                            <td style='text-align: left; width: 600px;'>{{ \App\ReportLineitem::find($operatingCashFlow->id)->line_item }}
                                            <td style='text-align: right;'>{{ $operatingCashFlow->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left;'>Net cash provided by (used in) operating activities
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['operating_total'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>CASH FLOWS FROM INVESTING ACTIVITIES
                                        <td>
                                    @foreach($investingCashFlows as $investingCashFlow)
                                        <tr>
                                            <td style='text-align: left; width: 600px;'>{{ \App\ReportLineitem::find($investingCashFlow->id)->line_item }}
                                            <td style='text-align: right;'>{{ $investingCashFlow->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left;'>Net cash provided by (used in) investing activities
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['investing_total'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>CASH FLOWS FROM FINANCING ACTIVITIES
                                        <td>
                                    @foreach($financingCashFlows as $financingCashFlow)
                                        <tr>
                                            <td style='text-align: left; width: 600px;'>{{ \App\ReportLineitem::find($financingCashFlow->id)->line_item }}
                                            <td style='text-align: right;'>{{ $financingCashFlow->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left;'>Net cash provided by (used in) financing activities
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['financing_total'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>NET INCREASE (DECREASE) IN CASH AND CASH EQUIVALENTS
                                        <td style='text-align: right;'>{{ $amounts['net_increase'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>CASH AND CASH EQUIVALENTS AT BEGINNING OF YEAR
                                        <td style='text-align: right; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['beg'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>CASH AND CASH EQUIVALENTS AT END OF YEAR
                                        <td style='text-align: right; border-bottom-style: double;'>{{ $amounts['end'] }}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
