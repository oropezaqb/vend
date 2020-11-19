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
                                    <input list="customer_ids" id="customer_id0" onchange="setValue(this)" data-id="" class="custom-select @error('customer_id') is-danger @enderror" required value="{!! old('customer_name') !!}">
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
                                                <input type="checkbox" id="myCheck2" onclick="myFunction2()">
                                            </th>
                                            <th>
                                                <label for="lines['invoice_id'][]">Description</label>
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
function showCustomer(str) {
  var xhttp;
  if (str == "") {
    document.getElementById("txtHint").innerHTML = "";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    document.getElementById("txtHint").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "getcustomer.php?q="+str, true);
  xhttp.send();
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
                                function addItemLines(a, b, c, d, e, f) {

                                    var tr = document.createElement("tr");
                                    var table = document.getElementById("item_lines");
                                    table.appendChild(tr);

                                    var td0 = document.createElement("td");
                                    tr.appendChild(td0);

                                    var check = document.createElement("input");
                                    check.setAttribute("type", "checkbox");
                                    check.setAttribute("class", "deleteBox2");
                                    td0.appendChild(check);

                                    var td1 = document.createElement("td");
                                    tr.appendChild(td1);

                                    var productInput = document.createElement("input");
                                    productInput.setAttribute("list", "product_ids");
                                    productInput.setAttribute("id", "item_lines['product_id'][]" + line2);
                                    productInput.setAttribute("onchange", "setValue(this)");
                                    productInput.setAttribute("data-id", line2);
                                    productInput.setAttribute("class", "custom-select");
                                    productInput.setAttribute("required", "required");
                                    productInput.setAttribute("value", f);
                                    td1.appendChild(productInput);

                                    var productHidden = document.createElement("input");
                                    productHidden.setAttribute("type", "hidden");
                                    productHidden.setAttribute("name", "item_lines['product_id'][]");
                                    productHidden.setAttribute("id", "item_lines['product_id'][]" + line2 + "-hidden");
                                    productHidden.setAttribute("value", a);
                                    td1.appendChild(productHidden);

                                    var productHidden2 = document.createElement("input");
                                    productHidden2.setAttribute("type", "hidden");
                                    productHidden2.setAttribute("name", "item_lines['product_name'][]");
                                    productHidden2.setAttribute("id", "name-item_lines['product_id'][]" + line2 + "-hidden");
                                    productHidden2.setAttribute("value", f);
                                    td1.appendChild(productHidden2);

                                    var td2 = document.createElement("td");
                                    tr.appendChild(td2);

                                    var descriptionInput = document.createElement("input");
                                    descriptionInput.setAttribute("type", "text");
                                    descriptionInput.setAttribute("class", "form-control");
                                    descriptionInput.setAttribute("id", "item_lines['description'][]" + line2);
                                    descriptionInput.setAttribute("name", "item_lines['description'][]");
                                    descriptionInput.setAttribute("style", "text-align: left;");
                                    descriptionInput.setAttribute("value", b);
                                    td2.appendChild(descriptionInput);

                                    var td3 = document.createElement("td");
                                    tr.appendChild(td3);

                                    var quantityInput = document.createElement("input");
                                    quantityInput.setAttribute("type", "number");
                                    quantityInput.setAttribute("class", "form-control");
                                    quantityInput.setAttribute("id", "item_lines['quantity'][]" + line2);
                                    quantityInput.setAttribute("name", "item_lines['quantity'][]");
                                    quantityInput.setAttribute("step", "0.001");
                                    quantityInput.setAttribute("style", "text-align: right;");
                                    quantityInput.setAttribute("value", c);
                                    td3.appendChild(quantityInput);

                                    var td4 = document.createElement("td");
                                    tr.appendChild(td4);

                                    var amountInput = document.createElement("input");
                                    amountInput.setAttribute("type", "number");
                                    amountInput.setAttribute("class", "form-control amount");
                                    amountInput.setAttribute("id", "item_lines['amount'][]" + line2);
                                    amountInput.setAttribute("name", "item_lines['amount'][]");
                                    amountInput.setAttribute("step", "0.01");
                                    amountInput.setAttribute("style", "text-align: right;");
                                    amountInput.setAttribute("value", d);
                                    amountInput.setAttribute("oninput", "updateSubtotal()");
                                    td4.appendChild(amountInput);

                                    var td5 = document.createElement("td");
                                    tr.appendChild(td5);

                                    var inputTaxInput = document.createElement("input");
                                    inputTaxInput.setAttribute("type", "number");
                                    inputTaxInput.setAttribute("class", "form-control tax");
                                    inputTaxInput.setAttribute("id", "item_lines['output_tax'][]" + line2);
                                    inputTaxInput.setAttribute("name", "item_lines['output_tax'][]");
                                    inputTaxInput.setAttribute("step", "0.01");
                                    inputTaxInput.setAttribute("style", "text-align: right;");
                                    inputTaxInput.setAttribute("value", e);
                                    inputTaxInput.setAttribute("oninput", "updateTotalTax()");
                                    td5.appendChild(inputTaxInput);

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
                                function updateSubtotal() {
                                    var x = document.getElementsByClassName("amount");
                                    var sum = 0;
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if(isNaN(parseInt(x[i].value))){
                                            continue;
                                        }
                                        sum += parseInt(x[i].value);
                                    }
                                    document.getElementById("subtotal").value = sum;
                                    updateTotal();
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
                                    var a = <?php echo json_encode(old("item_lines.'product_id'")); ?>;
                                    var b = <?php echo json_encode(old("item_lines.'description'")); ?>;
                                    var c = <?php echo json_encode(old("item_lines.'quantity'")); ?>;
                                    var d = <?php echo json_encode(old("item_lines.'amount'")); ?>;
                                    var e = <?php echo json_encode(old("item_lines.'output_tax'")); ?>;
                                    var f = <?php echo json_encode(old("item_lines.'product_name'")); ?>;
                                    var i;
                                    for (i = 0; i < a.length; i++)
                                    {
                                        if(a[i] == null) {a[i] = "";}
                                        if(b[i] == null) {b[i] = "";}
                                        if(c[i] == null) {c[i] = "";}
                                        if(d[i] == null) {d[i] = "";}
                                        if(e[i] == null) {e[i] = "";}
                                        if(f[i] == null) {f[i] = "";}
                                        addItemLines(a[i], b[i], c[i], d[i], e[i], f[i]);
                                    }
                                    updateSubtotal();
                                    updateTotalTax();
                                    updateTotal();
                                @endif
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
