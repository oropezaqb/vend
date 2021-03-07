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
                                        <td style='font-weight: bold; text-align: center;' colspan='2'>ASSETS
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>Current Assets
                                        <td>
                                    @foreach($currentAssets as $currentAsset)
                                        <tr>
                                            <td style='text-align: left; width: 360px;'>{{ \App\Lineitem::find($currentAsset->id)->name }}
                                            <td style='text-align: right;'>{{ $currentAsset->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left; text-indent: 25px;'>Total Current Assets
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['current_assets'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>Noncurrent Assets
                                        <td>
                                    @foreach($noncurrentAssets as $noncurrentAsset)
                                        <tr>
                                            <td style='text-align: left; width: 360px;'>{{ \App\Lineitem::find($noncurrentAsset->id)->name }}
                                            <td style='text-align: right;'>{{ $noncurrentAsset->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left; text-indent: 25px;'>Total Noncurrent Assets
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['noncurrent_assets'] }}
                                    <tr>
                                        <td style='text-align: left;'>Total Assets
                                        <td style='text-align: right; border-bottom-style: double;'>{{ $amounts['total_assets'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: center;' colspan='2'>LIABILITIES AND EQUITY
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>Current Liabilities
                                        <td>
                                    @foreach($currentLiabilities as $currentLiability)
                                        <tr>
                                            <td style='text-align: left; width: 360px;'>{{ \App\Lineitem::find($currentLiability->id)->name }}
                                            <td style='text-align: right;'>{{ $currentLiability->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left; text-indent: 25px;'>Total Current Liabilities
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['current_liabilities'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>Noncurrent Liabilities
                                        <td>
                                    @foreach($noncurrentLiabilities as $noncurrentLiability)
                                        <tr>
                                            <td style='text-align: left; width: 360px;'>{{ \App\Lineitem::find($noncurrentLiability->id)->name }}
                                            <td style='text-align: right;'>{{ $noncurrentLiability->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left; text-indent: 25px;'>Total Noncurrent Liabilities
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['noncurrent_liabilities'] }}
                                    <tr>
                                        <td style='text-align: left; text-indent: 25px;'>Total Liabilities
                                        <td style='text-align: right; border-bottom-style: solid; border-bottom-width:1px;;'>{{ $amounts['total_liabilities'] }}
                                    <tr>
                                        <td style='font-weight: bold; text-align: left;'>Equity
                                        <td>
                                    @foreach($equities as $equity)
                                        <tr>
                                            <td style='text-align: left; width: 360px;'>{{ \App\Lineitem::find($equity->id)->name }}
                                            <td style='text-align: right;'>{{ $equity->debit }}
                                    @endforeach
                                    @foreach($appropriatedREs as $appropriatedRE)
                                        <tr>
                                            <td style='text-align: left; width: 360px;'>{{ \App\Lineitem::find($appropriatedRE->id)->name }}
                                            <td style='text-align: right;'>{{ $appropriatedRE->debit }}
                                    @endforeach
                                    <tr>
                                        <td style='text-align: left; width: 360px;'>Unappropriated retained earnings
                                        <td style='text-align: right;'>{{ $amounts['retained_earnings'] }}
                                    <tr>
                                        <td style='text-align: left; text-indent: 25px;'>Total Equity
                                        <td style='text-align: right; border-top-style: solid; border-top-width:1px; border-bottom-style: solid; border-bottom-width:1px;'>{{ $amounts['total_equity'] }}
                                    <tr>
                                        <td style='text-align: left;'>Total Liabilities and Equity
                                        <td style='text-align: right; border-bottom-style: double;'>{{ $amounts['liabilities_equity'] }}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
