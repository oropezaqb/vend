@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add a New Cash Receipt)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="/cash_receipts">
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
                                <datalist id="account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value={{ $account->id }}>{{ $account->title }} ({{ $account->number }})</option>
                                    @endforeach
                                </datalist>
                                <datalist id="subsidiary_ledger_ids">
                                    @foreach ($subsidiaryLedgers as $subsidiaryLedger)
                                        <option data-value={{ $subsidiaryLedger->id }}>{{ $subsidiaryLedger->name }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-group custom-control-inline">
                                    <label for="account_id">Cash&nbsp;account</label>&nbsp;
                                    <input list="account_ids" id="account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('account_id') is-danger @enderror" required value="{!! old('account_name') !!}">
                                    <datalist id="account_ids">
                                        @foreach ($accounts as $account)
                                            <option data-value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="account_id" id="account_id0-hidden" value="{!! old('account_id') !!}">
                                    <input type="hidden" name="account_name" id="name-account_id0-hidden" value="{!! old('account_name') !!}">
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="date">Date&nbsp;</label>&nbsp;
                                    <input type="date" class="form-control @error('date') is-danger @enderror" id="date" name="date" required value="{!! old('date') !!}">
                                </div>
                                <div class="form-group custom-control-inline">
                                    <label for="number">Ref&nbsp;no.&nbsp;</label>&nbsp;
                                    <input type="number" class="form-control" id="doc_number" name="doc_number" style="text-align: right;"
                                        required value="{!! old('doc_number') !!}" oninput="getDocument()">
                                    <input type="hidden" name="doc_id" id="doc_id" value="{!! old('doc_id') !!}">
                                </div>
                                <br><br><br>
                                <div style="text-align: right;"><p>Amounts are Exclusive of Tax</p></div>
                                <h6 class="font-weight-bold">Add funds to this receipt</h6>
                                <div class="form-group">
                                    <table id="item_lines" style="width:100%">
                                        <tr style="text-align: center;">
                                            <th>
                                                <input type="checkbox" id="myCheck2" onclick="myFunction2()">
                                            </th>
                                            <th>
                                                <label for="item_lines['subsidiary_ledger_id'][]">Received&nbsp;from</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['account_id'][]">Account</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['description'][]">Description</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['amount'][]">Amount</label>
                                            </th>
                                            <th>
                                                <label for="item_lines['output_tax'][]">Tax</label>
                                            </th>
                                        <tr>
                                    </table>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" id="addItemLines" onclick="addItemLines('', '', '', '', '', '', '', '')">Add Lines</a>&nbsp;&nbsp;
                                <a class="btn btn-outline-secondary btn-sm" id="deleteItemLines" onclick="deleteItemLines()">Delete Lines</a>
                                <br><br>
                                <div class="form-group custom-control-inline">
                                    <label for="memo">Memo: </label>
                                    <textarea id="memo" name="memo" class="form-control" rows="2" cols="20" required>{!! old('memo') !!}</textarea>
                                </div>
                                <br>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="subtotal">Subtotal</label>&nbsp;
                                    <input id="subtotal" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="total_tax">Total&nbsp;tax</label>&nbsp;
                                    <input id="total_tax" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <br>
                                <div style="float: right; clear: both;">
                                    <div class="form-group custom-control-inline">
                                        <label for="cashback_account_id">Cash&nbsp;back&nbsp;goes&nbsp;to</label>&nbsp;
                                        <input list="account_ids" id="account_id1" onchange="setValue(this)" data-id="" class="custom-select @error('cashback_account_id') is-danger @enderror" required value="{!! old('cashback_account_name') !!}">
                                        <datalist id="account_ids">
                                            @foreach ($accounts as $account)
                                                <option data-value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </datalist>
                                        <input type="hidden" name="cashback_account_id" id="cashback_account_id0-hidden" value="{!! old('cashback_account_id') !!}">
                                        <input type="hidden" name="cashback_account_name" id="name-cashback_account_id0-hidden" value="{!! old('cashback_account_name') !!}">
                                    </div>
                                    <div class="form-group custom-control-inline">
                                        <label for="cashback_memo">Cash&nbsp;back&nbsp;memo</label>&nbsp;
                                        <textarea id="cashback_memo" name="cashback_memo" class="form-control" rows="1" cols="10" required>{!! old('cashback_memo') !!}</textarea>
                                    </div>
                                    <div class="form-group custom-control-inline">
                                        <label for="cashback_amount">Cash&nbsp;back&nbsp;amount</label>&nbsp;
                                        <input id="cashback_amount" type="text" width="15" style="text-align: right;" class="form-control" oninput="updateTotalTax()">
                                    </div>
                                </div>
                                <div class="form-group custom-control-inline" style="float: right; clear: both;">
                                    <label for="total">Total</label>&nbsp;
                                    <input id="total" type="text" width="15" readonly style="text-align: right;" class="form-control">
                                </div>
                                <br><br><br>
                                <button class="btn btn-primary" type="submit" style="float: right; clear: both;">Save</button>
                            </form>
                            <script>
                                var line = 0;
                                var line2 = 0;
                                var document = new Array();
                                var clines = new Array();
                                var ilines = new Array();
                                var suppliername = '';
                                var accounttitles = new Array();
                                var productnames = new Array();
                                var amounts = new Array();
                                addItemLines('', '', '', '', '', '', '');
                                function getDocument()
                                {
                                  var purchasable_doc = document.getElementById('purchasable_doc0-hidden').value;
                                  var doc_number = document.getElementById('doc_number').value;
                                  let _token = $('meta[name="csrf-token"]').attr('content');
                                  $.ajaxSetup({
                                    headers: {
                                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                  });
                                  $.ajax({
                                    type:'POST',
                                    url:'/suppliercredit/getdocument',
                                    data: {_token: _token, purchasable_doc: purchasable_doc, doc_number: doc_number},
                                    dataType: 'json',
                                    success:function(data) {
                                      doc = data.document;
                                      suppliername = data.suppliername;
                                      clines = data.clines;
                                      ilines = data.ilines;
                                      accounttitles = data.accounttitles;
                                      productnames = data.productnames;
                                      if (doc === null) {
                                          document.getElementById('supplier_id0').value = '';
                                          $(".clines").remove();
                                          $(".ilines").remove();
                                          updateSubtotal();
                                          updateTotalTax();
                                      }
                                      else {
                                          $(".clines").remove();
                                          $(".ilines").remove();
                                          displayDocument();
                                          updateSubtotal();
                                          updateTotalTax();
                                      }
                                    },
                                    error: function(data){
                                    }
                                  });
                                }
                                function displayDocument()
                                {
                                    document.getElementById('doc_id').value = doc['id'];
                                    document.getElementById('supplier_id0').value = suppliername;
                                    document.getElementById('name-supplier_id0-hidden').value = suppliername;
                                    for (index = 0; index < clines.length; ++index)
                                    {
                                        let accountid = clines[index]['account_id'];
                                        let accounttitle = accounttitles[index];
                                        let description = clines[index]['description'];
                                        if(description == null) {description = "";}
                                        addCategoryLines(accountid, description, null, null, accounttitle);
                                    }
                                    for (index = 0; index < ilines.length; ++index)
                                    {
                                        let productid = ilines[index]['product_id'];
                                        let productname = productnames[index];
                                        let description = ilines[index]['description'];
                                        if(description == null) {description = "";}
                                        addItemLines(productid, description, null, null, null, productname);
                                    }
                                }
                                function getAmounts(creditline)
                                {
                                  var purchasable_doc = document.getElementById('purchasable_doc0-hidden').value;
                                  var doc_id = document.getElementById('doc_id').value;
                                  var product_id = creditline.parentNode.parentNode.childNodes[1].childNodes[1].value;
                                  document.getElementById('product_id').value = product_id;
                                  var quantity_returned = creditline.value;
                                  document.getElementById('quantity_returned').value = quantity_returned;
                                  let _token = $('meta[name="csrf-token"]').attr('content');
                                  $.ajaxSetup({
                                    headers: {
                                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                  });
                                  $.ajax({
                                    type:'POST',
                                    url:'/suppliercredit/getamounts',
                                    data: {_token: _token, purchasable_doc: purchasable_doc, doc_id: doc_id, product_id: product_id, quantity_returned: quantity_returned},
                                    dataType: 'json',
                                    success:function(data) {
                                      amounts = data.amounts;
                                      if (amounts === null) {
                                          creditline.parentNode.parentNode.childNodes[4].childNodes[0].value = '';
                                          creditline.parentNode.parentNode.childNodes[5].childNodes[0].value = '';
                                      }
                                      else {
                                          displayAmounts(creditline, amounts);
                                      }
                                    },
                                    error: function(data){
                                    }
                                  });
                                }
                                function displayAmounts(creditline, amounts)
                                {
                                    creditline.parentNode.parentNode.childNodes[4].childNodes[0].value = amounts["amount"];
                                    creditline.parentNode.parentNode.childNodes[5].childNodes[0].value = amounts["tax"];
                                    updateSubtotal();
                                    updateTotalTax();
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
                                function addItemLines(a, b, c, d, e, f, g) {
                                    var tr = document.createElement("tr");
                                    tr.setAttribute("class", "ilines");
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
                                    var subLedgerInput = document.createElement("input");
                                    subLedgerInput.setAttribute("list", "subsidiary_ledger_ids");
                                    subLedgerInput.setAttribute("id", "item_lines['subsidiary_ledger_id'][]" + line2);
                                    subLedgerInput.setAttribute("onchange", "setValue(this)");
                                    subLedgerInput.setAttribute("data-id", line2);
                                    subLedgerInput.setAttribute("class", "custom-select");
                                    subLedgerInput.setAttribute("required", "required");
                                    subLedgerInput.setAttribute("value", f);
                                    td1.appendChild(subLedgerInput);
                                    var subLedgerHidden = document.createElement("input");
                                    subLedgerHidden.setAttribute("type", "hidden");
                                    subLedgerHidden.setAttribute("name", "item_lines['subsidiary_ledger_id'][]");
                                    subLedgerHidden.setAttribute("id", "item_lines['subsidiary_ledger_id'][]" + line2 + "-hidden");
                                    subLedgerHidden.setAttribute("value", a);
                                    td1.appendChild(subLedgerHidden);
                                    var subLedgerHidden2 = document.createElement("input");
                                    subLedgerHidden2.setAttribute("type", "hidden");
                                    subLedgerHidden2.setAttribute("name", "item_lines['subsidiary_ledger_name'][]");
                                    subLedgerHidden2.setAttribute("id", "name-item_lines['subsidiary_ledger_id'][]" + line2 + "-hidden");
                                    subLedgerHidden2.setAttribute("value", f);
                                    td1.appendChild(subLedgerHidden2);
                                    var td2 = document.createElement("td");
                                    tr.appendChild(td2);
                                    var accountInput = document.createElement("input");
                                    accountInput.setAttribute("list", "account_ids");
                                    accountInput.setAttribute("id", "item_lines['account_id'][]" + line2);
                                    accountInput.setAttribute("onchange", "setValue(this)");
                                    accountInput.setAttribute("data-id", line2);
                                    accountInput.setAttribute("class", "custom-select");
                                    accountInput.setAttribute("required", "required");
                                    accountInput.setAttribute("value", g);
                                    td2.appendChild(accountInput);
                                    var accountHidden = document.createElement("input");
                                    accountHidden.setAttribute("type", "hidden");
                                    accountHidden.setAttribute("name", "item_lines['account_id'][]");
                                    accountHidden.setAttribute("id", "item_lines['account_id'][]" + line2 + "-hidden");
                                    accountHidden.setAttribute("value", b);
                                    td2.appendChild(accountHidden);
                                    var accountHidden2 = document.createElement("input");
                                    accountHidden2.setAttribute("type", "hidden");
                                    accountHidden2.setAttribute("name", "item_lines['account_name'][]");
                                    accountHidden2.setAttribute("id", "name-item_lines['account_id'][]" + line2 + "-hidden");
                                    accountHidden2.setAttribute("value", g);
                                    td2.appendChild(accountHidden2);
                                    var td3 = document.createElement("td");
                                    tr.appendChild(td3);
                                    var descriptionInput = document.createElement("input");
                                    descriptionInput.setAttribute("type", "text");
                                    descriptionInput.setAttribute("class", "form-control");
                                    descriptionInput.setAttribute("id", "item_lines['description'][]" + line2);
                                    descriptionInput.setAttribute("name", "item_lines['description'][]");
                                    descriptionInput.setAttribute("style", "text-align: left;");
                                    descriptionInput.setAttribute("value", b);
                                    td3.appendChild(descriptionInput);
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
                                    inputTaxInput.setAttribute("id", "item_lines['ouput_tax'][]" + line2);
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
                                function deleteCategoryLines () {
                                    var x = document.getElementsByClassName("deleteBox");
                                    var i;
                                    for (i = 0; i < x.length; i++) {
                                        if (x[i].checked) {
                                            x[i].parentNode.parentNode.remove();
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
                                    cashback_amount = parseInt(document.getElementById("cashback_amount").value);
                                    if(isNaN(subtotal)) {
                                        subtotal = 0;
                                    }
                                    if(isNaN(tax)) {
                                        tax = 0;
                                    }
                                    if(isNaN(cashback_amount)) {
                                        cashback_amount = 0;
                                    }
                                    document.getElementById("total").value = subtotal + tax - cashback_amount;
                                }
                                @if (!empty(old('category_lines')))
                                    var a = <?php echo json_encode(old("category_lines.'account_id'")); ?>;
                                    var b = <?php echo json_encode(old("category_lines.'description'")); ?>;
                                    var c = <?php echo json_encode(old("category_lines.'amount'")); ?>;
                                    var d = <?php echo json_encode(old("category_lines.'input_tax'")); ?>;
                                    var e = <?php echo json_encode(old("category_lines.'account_name'")); ?>;
                                    var i;
                                    for (i = 0; i < a.length; i++)
                                    {
                                        if(a[i] == null) {a[i] = "";}
                                        if(b[i] == null) {b[i] = "";}
                                        if(c[i] == null) {c[i] = "";}
                                        if(d[i] == null) {d[i] = "";}
                                        if(e[i] == null) {e[i] = "";}
                                        addCategoryLines(a[i], b[i], c[i], d[i], e[i]);
                                    }
                                    updateSubtotal();
                                    updateTotalTax();
                                    updateTotal();
                                @else
                                    @if (!empty($supplierCredit->clines))
                                        @foreach ($supplierCredit->clines as $categoryLine)
                                            var categoryLine = <?php echo json_encode($categoryLine); ?>;
                                            var a = categoryLine['account_id'];
                                            var b = categoryLine['description'];
                                            var c = categoryLine['amount'];
                                            var d = categoryLine['input_tax'];
                                            var e = <?php echo json_encode(\App\Account::where('id', $categoryLine->account_id)->firstOrFail()->title); ?>;
                                            if(a == null) {a = "";}
                                            if(b == null) {b = "";}
                                            if(c == null) {c = "";}
                                            if(d == null) {d = "";}
                                            if(e == null) {e = "";}
                                            addCategoryLines(a, b, c, d, e);
                                            updateTotals();
                                        @endforeach
                                    @endif
                                @endif
                                @if (!empty(old('item_lines')))
                                    var a = <?php echo json_encode(old("item_lines.'product_id'")); ?>;
                                    var b = <?php echo json_encode(old("item_lines.'description'")); ?>;
                                    var c = <?php echo json_encode(old("item_lines.'quantity'")); ?>;
                                    var d = <?php echo json_encode(old("item_lines.'amount'")); ?>;
                                    var e = <?php echo json_encode(old("item_lines.'input_tax'")); ?>;
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
                                @else
                                    @if (!empty($supplierCredit->ilines))
                                        @foreach ($supplierCredit->ilines as $itemLine)
                                            var itemLine = <?php echo json_encode($itemLine); ?>;
                                            var a = itemLine['subsidiary_ledger_id'];
                                            var b = itemLine['account_id'];
                                            var c = itemLine['description'];
                                            var d = itemLine['amount'];
                                            var e = itemLine['input_tax'];
                                            var f = <?php echo json_encode(\App\Product::where('id', $itemLine->product_id)->firstOrFail()->name); ?>;
                                            if(a == null) {a = "";}
                                            if(b == null) {b = "";}
                                            if(c == null) {c = "";}
                                            if(d == null) {d = "";}
                                            if(e == null) {e = "";}
                                            if(f == null) {f = "";}
                                            addItemLines(a, b, c, d, e, f);
                                            updateTotals();
                                        @endforeach
                                    @endif
                                @endif
                                function updateTotals() {
                                    updateSubtotal();
                                    updateTotalTax();
                                    updateTotal();
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
