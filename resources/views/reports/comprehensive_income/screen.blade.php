@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">{{ auth()->user()->currentCompany->company->name }}</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <h6 style='text-align:center;'>{{ auth()->user()->currentCompany->company->name }}</h6>
                            <h5 style='font-weight: bold;text-align:center;'>{{ $query->title }}</h5>
                            <p style='text-align:center;'>{{ $query->date }}</p>
                            <div class="run">
                                <table border=0 cellpadding=5 cellspacing=0 align=center style='border-collapse: collapse; border: 0px solid rgb(192, 192, 192);'>
                                    <tr>
                                        <td style='text-align: left; width: 360px;'>Revenue
                                        <td style='text-align: right;'>{{ $amounts['revenue'] }}
                                    <tr>
                                        <td style='text-align: left;'>Other income
                                        <td style='text-align: right;'>{{ $amounts['other_income'] }}
                                    <tr>
                                        <td style='text-align: left;'>Total income
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px;'>{{ $amounts['total_income'] }}
                                    @if ($amounts['cost_of_goods_sold'] != 0)
                                        <tr>
                                            <td style='text-align: left;'>Cost of goods sold
                                            <td style='text-align: right;'>{{ $amounts['cost_of_goods_sold'] }}
                                        <tr>
                                            <td style='text-align: left;'>Gross profit
                                            <td style='text-align: right; border-top-style: solid; border-top-width:1px;'>{{ $amounts['gross_profit'] }}
                                    @endif
                                    <tr>
                                        <td style='text-align: left;'>Expenses
                                        <td style='text-align: right;'>{{ $amounts['expenses'] }}
                                    <tr>
                                        <td style='text-align: left;'>Profit before tax
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px;'>{{ $amounts['profit_before_tax'] }}
                                    <tr>
                                        <td style='text-align: left;'>Income tax
                                        <td style='text-align: right;'>{{ $amounts['income_tax'] }}
                                    <tr>
                                        <td style='text-align: left;'>Net income
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px;'>{{ $amounts['net_income'] }}
                                    <tr>
                                        <td style='text-align: left;'>Other comprehensive income
                                        <td style='text-align: right;'>{{ $amounts['other_comprehensive_income'] }}
                                    <tr>
                                        <td style='text-align: left;'>Total comprehensive income
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: double;'>{{ $amounts['total_comprehensive_income'] }}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
