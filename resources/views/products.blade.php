<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #edf2f9;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0 !important;
        }
        .table > :not(caption) > * > * {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #f8f9fa;
            color: #5e6e82;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f9;
        }
        .btn-action {
            padding: 4px 8px;
            font-size: 14px;
            border-radius: 6px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 45px;
            color: #dee2e6;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-box-seam text-primary fs-2 me-3"></i>
                <div>
                    <h2 class="mb-0 fw-bold">Inventory System</h2>
                    <p class="text-muted mb-0">Track and update stock records</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2"></i>Product Entry</h5>
                </div>
                <div class="card-body p-4">
                    <form id="mainForm">
                        <input type="hidden" id="editId">
                        <div class="row g-4 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">PRODUCT NAME</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control" id="itemName" placeholder="e.g. Graphics Card" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-bold">QTY IN STOCK</label>
                                <input type="number" class="form-control" id="itemQty" min="0" placeholder="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-bold">PRICE EACH</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" class="form-control" id="itemPrice" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="saveBtn">
                                    <i class="bi bi-save me-1"></i> <span>Save</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th>Date Added</th>
                                    <th class="text-end">Total Val</th>
                                    <th class="text-center">Options</th>
                                </tr>
                            </thead>
                            <tbody id="dataContainer">
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold text-muted uppercase">Grand Total:</td>
                                    <td id="totalDisplay" class="text-end fw-bold fs-5 text-primary">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainForm = document.getElementById('mainForm');
    const dataContainer = document.getElementById('dataContainer');
    const totalDisplay = document.getElementById('totalDisplay');
    const saveBtn = document.getElementById('saveBtn');
    
    let appData = [];
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function formatMoney(amount) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
    }

    function popToast(msg, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: msg,
            showConfirmButton: false,
            timer: 2500
        });
    }

    function buildTable(records) {
        appData = records;
        dataContainer.innerHTML = '';
        let runningTotal = 0;

        if (records.length === 0) {
            dataContainer.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i><h5>No items yet</h5></div></td></tr>`;
            totalDisplay.textContent = formatMoney(0);
            return;
        }

        records.forEach(function(row) {
            let rowTotal = row.quantity * row.price;
            runningTotal += rowTotal;

            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-medium">${row.name}</td>
                <td class="text-center"><span class="badge bg-secondary rounded-pill px-3">${row.quantity}</span></td>
                <td class="text-end">${formatMoney(row.price)}</td>
                <td class="text-muted small">${new Date(row.submitted_at).toLocaleString()}</td>
                <td class="text-end fw-semibold">${formatMoney(rowTotal)}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-action me-1" onclick="setupEdit('${row.id}')"><i class="bi bi-pencil-square"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="removeRecord('${row.id}')"><i class="bi bi-trash"></i></button>
                </td>
            `;
            dataContainer.appendChild(tr);
        });

        totalDisplay.textContent = formatMoney(runningTotal);
    }

    async function fetchData() {
        try {
            let res = await fetch('/api/products');
            let result = await res.json();
            buildTable(result);
        } catch (err) {
            console.log('Error fetching products:', err);
        }
    }

    window.setupEdit = function(id) {
        let target = appData.find(i => i.id === id);
        if(!target) return;

        document.getElementById('editId').value = target.id;
        document.getElementById('itemName').value = target.name;
        document.getElementById('itemQty').value = target.quantity;
        document.getElementById('itemPrice').value = target.price;
        
        saveBtn.querySelector('span').textContent = 'Update';
        saveBtn.classList.remove('btn-primary');
        saveBtn.classList.add('btn-success');
    };

    window.removeRecord = async function(id) {
        let confirmBox = await Swal.fire({
            title: 'Delete this?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes'
        });

        if (confirmBox.isConfirmed) {
            try {
                let req = await fetch(`/api/products/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });

                if (req.ok) {
                    let parsed = await req.json();
                    buildTable(parsed.data);
                    popToast('Deleted');
                    
                    if (document.getElementById('editId').value === id) {
                        mainForm.reset();
                        document.getElementById('editId').value = '';
                        saveBtn.querySelector('span').textContent = 'Save';
                        saveBtn.className = 'btn btn-primary w-100';
                    }
                }
            } catch (err) {
                popToast('Error connecting', 'error');
            }
        }
    };

    mainForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        saveBtn.disabled = true;
        let originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = 'Wait...';
        
        let recordId = document.getElementById('editId').value;
        let postData = {
            name: document.getElementById('itemName').value,
            quantity: document.getElementById('itemQty').value,
            price: document.getElementById('itemPrice').value
        };

        let endpoint = recordId ? `/api/products/${recordId}` : '/api/products';
        let actionMethod = recordId ? 'PUT' : 'POST';

        try {
            let req = await fetch(endpoint, {
                method: actionMethod,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(postData)
            });

            if (req.ok) {
                let parsed = await req.json();
                buildTable(parsed.data);
                
                mainForm.reset();
                document.getElementById('editId').value = '';
                saveBtn.className = 'btn btn-primary w-100';
                
                popToast(recordId ? 'Updated' : 'Saved');
            } else {
                popToast('Failed to save', 'error');
            }
        } catch (err) {
            console.log(err);
            popToast('Network error', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = recordId ? '<i class="bi bi-save me-1"></i> <span>Save</span>' : originalText;
        }
    });

    fetchData();
});
</script>
</body>
</html>