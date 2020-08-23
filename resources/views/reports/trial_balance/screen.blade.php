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
                                @if (!is_null($stmt))
                                    <table border=0 cellpadding=5 cellspacing=0 align=center style='border-collapse: collapse; border: 0px solid rgb(192, 192, 192);'>
                                        <tr>
                                            <th style='text-align: left;'>Account Title
                                            <th style='text-align: right;'>Debit
                                            <th style='text-align: right;'>Credit
                                        @php
                                            $total_debit = 0;
                                            $total_credit = 0;
                                            while ($row = $stmt->fetch()) {
                                                echo '<tr>';
                                                echo "<td style='min-width:200px'>$row[title]</td>";
                                                if ($row['debit'] > 0)
                                                {
                                                    echo "<td style='text-align:right; min-width:100px'>" . +$row['debit'];
                                                    echo "<td style='text-align:right; min-width:100px'>";
                                                    $total_debit += +$row['debit'];
                                                }
                                                else
                                                {
                                                    echo "<td style='text-align:right; min-width:100px'>";
                                                    echo "<td style='text-align:right; min-width:100px'>" . -$row['debit'];
                                                    $total_credit += -$row['debit'];
                                                }
                                            }
                                        @endphp
                                        <tr><td>Total<td style='text-align:right; border-bottom-style: double; border-top-style: solid; border-top-width:1px;'>{{ $total_debit }}<td style='text-align:right; border-bottom-style: double; border-top-style: solid; border-top-width:1px;'>{{ $total_credit }}
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
