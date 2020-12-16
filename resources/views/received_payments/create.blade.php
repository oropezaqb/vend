@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add a New Receipt of Payment)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="/received_payments">
                                @csrf
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <datalist id="customer_ids">
                                    @foreach ($customers as $customer)
                                        <option data-value={{ $customer->id }}>{{ $customer->name }}</option>
                                    @endforeach
                                </datalist>
                                <datalist id="account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value={{ $account->id }}>{{ $account->title }} ({{ $account->number }})</option>
                                    @endforeach
                                </datalist>
                                <div class="form-group custom-control-inline">
                                    <label for="customer_id">Customer</label>&nbsp;
                                    <input list="customer_ids" id="customer_id0" oninput="setValue(this); getInvoices();" data-id="" class="custom-select @error('customer_id') is-danger @enderror" required value="{!! old('customer_name') !!}">
                                    <datalist id="customer_ids">
                                        @foreach ($customers as $customer)
                                            <option data-value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="customer_id" id="customer_id0-hidden" value="{!! old('customer_id') !!}">
                                    <input type="hidden" name="customer_name" id="name-customer_id0-hidden" value="{!! old('customer_name') !!}">
                                </div>
                                <br><br>
                                <div class="form-group custom-control-inline">
                                    <label for="date">Payment&nbsp;date&nbsp;</label>&nbsp;
                                    <input type="date" class="form-control @error('date') is-danger @enderror" id="date" name="date" required value="{!! old('date') !!}">
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="number">Receipt&nbsp;no.&nbsp;</label>&nbsp;
                                    <input type="number" class="form-control" id="number" name="number" style="text-align: right;" required value="{!! old('number') !!}">
                                </div>
                                <br><br>
                                <div class="form-group custom-control-inline">
                                    <label for="account_id">Deposit&nbsp;to</label>&nbsp;
                                    <input list="account_ids" id="account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('account_id') is-danger @enderror" required value="{!! old('account_name') !!}">
                                    <datalist id="account_ids">
                                        @foreach ($accounts as $account)
                                            <option data-value="{{ $account->id }}">{{ $account->title }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="account_id" id="account_id0-hidden" value="{!! old('account_id') !!}">
                                    <input type="hidden" name="account_name" id="name-account_id0-hidden" value="{!! old('account_name') !!}">
                                </div>
                                <br><br>
                                <h6 class="font-weight-bold">Outstanding Transactions</h6>
                                <div class="form-group">
                                    <table id="item_lines" style="width:100%">
                                        <tr style="text-align: center;">
                                            <th>
                                                <label>Description</label>
                                            </th>
                                            <th>
                                                <label for="lines['due_date'][]">Due Date</label>
                                            </th>
                                            <th>
                                                <label for="lines['original_amount'][]">Original Amount</label>
                                            </th>
                                            <th>
                                                <label for="lines['open_balance'][]">Open Balance</label>
                                            </th>
                                            <th>
                                                <label for="lines['payment'][]">Payment</label>
                                            </th>
                                        <tr>
                                    </table>
                                </div>
                                <br><br>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="subtotal">Total</label>&nbsp;
                                    <input id="subtotal" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <br><br><br>
                                <button class="btn btn-primary" type="submit" style="float: right; clear: both;">Save</button>
                            </form>
                            <script>
                                var line = 0;
                                var line2 = 0;
                                var invoice_ids = new Array();
                                function getInvoices() {
                                  var customer_id = document.getElementById('customer_id0-hidden').value;
                                  let _token = $('meta[name="csrf-token"]').attr('content');
                                  $.ajaxSetup({
                                    headers: {
                                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                  });
                                  $.ajax({
                                    type:'POST',
                                    url:'/received_payments/ajax-request',
                                    data: {_token: _token, customer_id: customer_id},
                                    dataType: 'json',
                                    success:function(data) {
                                      invoice_ids = data.invoices;
                                      if (invoice_ids === null) {
                                          $(".invoice-lines").remove();
                                          deleteInvoices();
                                      }
                                      else {
                                          $(".invoice-lines").remove();
                                          displayInvoices();
                                      }
                                    },
                                    error: function(data){
                                    }
                                  });
                                }
                                function deleteInvoices()
                                {
                                    var x = document.getElementsByClassName("invoice-lines");
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        x[i].remove();
                                    }
                                }
                                function displayInvoices()
                                {
                                    for (index = 0; index < invoice_ids.length; ++index)
                                    {
                                        let invoice_id = invoice_ids[index]['invoice_id'];
                                        let number = invoice_ids[index]['number'];
                                        let date = invoice_ids[index]['date'];
                                        let due_date = invoice_ids[index]['due_date'];
                                        let amount = invoice_ids[index]['amount'];
                                        let balance = invoice_ids[index]['balance'];
                                        addItemLines(invoice_id, number, date, due_date, amount, balance);
                                    }
                                }
                                function setValue (id)
                                {
                                    var input = id,
                                        list = input.getAttribute('list'),
                                        options = document.querySelectorAll('#' + list + ' option'),
                                        hiddenInput = document.getElementById(input.getAttribute('id') + '-hidden'),
                                        hiddenInputName = document.getElementById('name-' + input.getAttribute('id') + '-hidden'),
                                        label = input.value;

                                    hiddenInputName.value = label;
                                    hiddenInput.value = label;

                                    for(var i = 0; i < options.length; i++) {
                                        var option = options[i];

                                        if(option.innerText === label) {
                                            hiddenInput.value = option.getAttribute('data-value');
                                            break;
                                        }
                                    }
                                }
                                function addItemLines(invoice_id, number, date, due_date, amount, balance, payment)
                                {
                                    var tr = document.createElement("tr");
                                    tr.setAttribute("class", "invoice-lines");
                                    var table = document.getElementById("item_lines");
                                    table.appendChild(tr);

                                    var td1 = document.createElement("td");
                                    tr.appendChild(td1);

                                    var invoiceNumber = document.createElement("input");
                                    invoiceNumber.setAttribute("type", "text");
                                    invoiceNumber.setAttribute("id", "item_lines['invoice_id'][]" + line2);
                                    invoiceNumber.setAttribute("class", "form-control");
                                    invoiceNumber.setAttribute("value", "Invoice " + number + " (" + date + ")");
                                    invoiceNumber.setAttribute("readonly", "readonly");
                                    td1.appendChild(invoiceNumber);

                                    var invoiceHidden = document.createElement("input");
                                    invoiceHidden.setAttribute("type", "hidden");
                                    invoiceHidden.setAttribute("name", "item_lines['invoice_id'][]");
                                    invoiceHidden.setAttribute("id", "item_lines['invoice_id'][]" + line2 + "-hidden");
                                    invoiceHidden.setAttribute("value", invoice_id);
                                    td1.appendChild(invoiceHidden);

                                    var numberHidden = document.createElement("input");
                                    numberHidden.setAttribute("type", "hidden");
                                    numberHidden.setAttribute("name", "item_lines['number'][]");
                                    numberHidden.setAttribute("id", "item_lines['number'][]" + line2 + "-hidden");
                                    numberHidden.setAttribute("value", number);
                                    td1.appendChild(numberHidden);

                                    var dateHidden = document.createElement("input");
                                    dateHidden.setAttribute("type", "hidden");
                                    dateHidden.setAttribute("name", "item_lines['date'][]");
                                    dateHidden.setAttribute("id", "item_lines['date'][]" + line2 + "-hidden");
                                    dateHidden.setAttribute("value", date);
                                    td1.appendChild(dateHidden);

                                    var td2 = document.createElement("td");
                                    tr.appendChild(td2);

                                    var dueDate = document.createElement("input");
                                    dueDate.setAttribute("type", "text");
                                    dueDate.setAttribute("class", "form-control");
                                    dueDate.setAttribute("id", "item_lines['due_date'][]" + line2);
                                    dueDate.setAttribute("name", "item_lines['due_date'][]");
                                    dueDate.setAttribute("style", "text-align: left;");
                                    dueDate.setAttribute("value", due_date);
                                    dueDate.setAttribute("readonly", "readonly");
                                    td2.appendChild(dueDate);

                                    var td3 = document.createElement("td");
                                    tr.appendChild(td3);

                                    var amountOriginal = document.createElement("input");
                                    amountOriginal.setAttribute("type", "number");
                                    amountOriginal.setAttribute("class", "form-control");
                                    amountOriginal.setAttribute("id", "item_lines['amount'][]" + line2);
                                    amountOriginal.setAttribute("name", "item_lines['amount'][]");
                                    amountOriginal.setAttribute("step", "0.01");
                                    amountOriginal.setAttribute("style", "text-align: right;");
                                    amountOriginal.setAttribute("value", amount);
                                    amountOriginal.setAttribute("readonly", "readonly");
                                    td3.appendChild(amountOriginal);

                                    var td4 = document.createElement("td");
                                    tr.appendChild(td4);

                                    var openBalance = document.createElement("input");
                                    openBalance.setAttribute("type", "number");
                                    openBalance.setAttribute("class", "form-control");
                                    openBalance.setAttribute("id", "item_lines['balance'][]" + line2);
                                    openBalance.setAttribute("name", "item_lines['balance'][]");
                                    openBalance.setAttribute("step", "0.01");
                                    openBalance.setAttribute("style", "text-align: right;");
                                    openBalance.setAttribute("value", balance);
                                    openBalance.setAttribute("readonly", "readonly");
                                    td4.appendChild(openBalance);

                                    var td5 = document.createElement("td");
                                    tr.appendChild(td5);

                                    var paymentAmount = document.createElement("input");
                                    paymentAmount.setAttribute("type", "number");
                                    paymentAmount.setAttribute("class", "form-control payment");
                                    paymentAmount.setAttribute("id", "item_lines['payment'][]" + line2);
                                    paymentAmount.setAttribute("name", "item_lines['payment'][]");
                                    paymentAmount.setAttribute("step", "0.01");
                                    paymentAmount.setAttribute("style", "text-align: right;");
                                    paymentAmount.setAttribute("value", payment);
                                    paymentAmount.setAttribute("oninput", "updateSubTotal()");
                                    td5.appendChild(paymentAmount);

                                    line2++;
                                }
                                function myFunction() {
                                    var checkBox = document.getElementById("myCheck");
                                    if (checkBox.checked == true)
                                    {
                                        var x = document.getElementsByClassName("deleteBox");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = true;
                                        }
                                    }
                                    else
                                    {
                                        var x = document.getElementsByClassName("deleteBox");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = false;
                                        }
                                    }
                                }
                                function myFunction2() {
                                    var checkBox = document.getElementById("myCheck2");
                                    if (checkBox.checked == true)
                                    {
                                        var x = document.getElementsByClassName("deleteBox2");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = true;
                                        }
                                    }
                                    else
                                    {
                                        var x = document.getElementsByClassName("deleteBox2");
                                        var i;
                                        for (i = 0; i < x.length; i++) {
                                            x[i].checked = false;
                                        }
                                    }
                                }
                                function deleteItemLines () {
                                    var x = document.getElementsByClassName("deleteBox2");
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if (x[i].checked) {
                                            x[i].parentNode.parentNode.remove();
                                        }
                                    }
                                }
                                function updateSubTotal() {
                                    var x = document.getElementsByClassName("payment");
                                    var sum = 0;
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if(isNaN(parseInt(x[i].value))){
                                            continue;
                                        }
                                        sum += parseInt(x[i].value);
                                    }
                                    document.getElementById("subtotal").value = sum;
                                }
                                function updateTotalTax() {
                                    var x = document.getElementsByClassName("tax");
                                    var sum = 0;
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if(isNaN(parseInt(x[i].value))){
                                            continue;
                                        }
                                        sum += parseInt(x[i].value);
                                    }
                                    document.getElementById("total_tax").value = sum;
                                    updateTotal();
                                }
                                function updateTotal() {
                                    subtotal = parseInt(document.getElementById("subtotal").value);
                                    tax = parseInt(document.getElementById("total_tax").value);
                                    if(isNaN(tax)) {
                                        document.getElementById("total").value = subtotal;
                                    }
                                    else {
                                        if(isNaN(subtotal)) {
                                            document.getElementById("total").value = tax;
                                        }
                                        else {
                                            document.getElementById("total").value = subtotal + tax;
                                        }
                                    }
                                }
                                @if (!empty(old('item_lines')))
console.log(<?php echo json_encode(old("item_lines")); ?>);
                                    var a = <?php echo json_encode(old("item_lines.'invoice_id'")); ?>;
                                    var b = <?php echo json_encode(old("item_lines.'number'")); ?>;
                                    var c = <?php echo json_encode(old("item_lines.'date'")); ?>;
                                    var d = <?php echo json_encode(old("item_lines.'due_date'")); ?>;
                                    var e = <?php echo json_encode(old("item_lines.'amount'")); ?>;
                                    var f = <?php echo json_encode(old("item_lines.'balance'")); ?>;
                                    var g = <?php echo json_encode(old("item_lines.'payment'")); ?>;
                                    var i;
                                    for (i = 0; i < a.length; i++)
                                    {
                                        if(a[i] == null) {a[i] = "";}
                                        if(b[i] == null) {b[i] = "";}
                                        if(c[i] == null) {c[i] = "";}
                                        if(d[i] == null) {d[i] = "";}
                                        if(e[i] == null) {e[i] = "";}
                                        if(f[i] == null) {f[i] = "";}
                                        if(g[i] == null) {g[i] = "";}
                                        addItemLines(a[i], b[i], c[i], d[i], e[i], f[i], g[i]);
                                    }
                                    updateSubTotal();
                                @endif
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
