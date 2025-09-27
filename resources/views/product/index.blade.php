<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Dropzone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-primary);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: var(--shadow-md);
            border: none;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .main-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 1px solid var(--border-color);
            border-radius: 12px 12px 0 0;
            padding: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .page-subtitle {
            color: var(--text-secondary);
            margin: 0.5rem 0 0 0;
            font-size: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline-secondary {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-secondary:hover {
            background-color: var(--light-bg);
            border-color: var(--secondary-color);
            color: var(--text-primary);
        }

        .table-container {
            padding: 2rem;
        }

        .dataTables_wrapper {
            margin-top: 1rem;
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table thead th {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            border: none;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--border-color);
        }

        .no-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            font-size: 1.5rem;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 12px 12px 0 0;
            border: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
        }

        /* Enhanced Progress Bar Styles */
        .progress {
            height: 28px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #cbd5e1;
        }

        .progress-bar {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 50%, #047857 100%);
            border-radius: 14px;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 50%, rgba(0, 0, 0, 0.1) 100%);
            border-radius: 14px;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            z-index: 10;
            letter-spacing: 0.5px;
        }

        .progress-container {
            margin: 2rem 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .progress-label i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }

        /* Fixed Stats Grid Layout */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all 0.2s ease;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stats-number {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0;
            line-height: 1;
        }

        .stats-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0.75rem 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .stats-card {
                padding: 1rem 0.75rem;
                min-height: 80px;
            }

            .stats-number {
                font-size: 1.8rem;
            }

            .progress-container {
                padding: 1rem;
                margin: 1rem 0;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border-left: 4px solid var(--primary-color);
        }

        .dropzone {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            background: var(--light-bg);
            padding: 2rem;
            text-align: center;
            transition: all 0.2s ease;
        }

        .dropzone:hover {
            border-color: var(--primary-color);
            background: #f0f9ff;
        }

        .dropzone.dz-drag-hover {
            border-color: var(--success-color);
            background: #f0fdf4;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dataTables_filter input {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
        }

        .dataTables_length select {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.25rem 0.5rem;
        }

        .page-item .page-link {
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 6px;
            margin: 0 2px;
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-item .page-link:hover {
            background: var(--light-bg);
            border-color: var(--secondary-color);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes me-2"></i>
                Product Management System
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="main-container fade-in">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="page-title">
                            <i class="fas fa-cube me-3"></i>
                            Product Catalog
                        </h1>
                        <p class="page-subtitle">Manage your product inventory with ease</p>
                    </div>
                    <div class="col-md-4">
                        <div class="action-buttons">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-2"></i>
                                Upload CSV
                            </button>
                            <button class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-2"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="table-container">
                <div class="table-responsive">
                    <table id="productsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th><i class="fas fa-barcode me-2"></i>SKU</th>
                                <th><i class="fas fa-tag me-2"></i>Name</th>
                                <th><i class="fas fa-align-left me-2"></i>Description</th>
                                <th><i class="fas fa-dollar-sign me-2"></i>Price</th>
                                <th><i class="fas fa-image me-2"></i>Primary Image</th>
                                <th><i class="fas fa-calendar me-2"></i>Created At</th>
                                <th><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CSV Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-csv me-2"></i>
                        Upload Product CSV
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="csvUploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="csv_file" class="form-label">
                                <i class="fas fa-file me-2"></i>
                                Select CSV File
                            </label>
                            <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Supported format: CSV files only. Maximum size: 1GB
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload me-2"></i>
                                Upload & Process
                            </button>
                        </div>
                    </form>

                    <!-- Fixed Progress Bar -->
                    <div class="progress-container" style="display:none;" id="progressContainer">
                        <div class="progress-label">
                            <span><i class="fas fa-upload me-1"></i>Processing CSV File</span>
                            <span id="progressText">0%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width:0%" id="progressBar">
                                <span class="progress-text" id="progressTextInner">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Box -->
                    <div class="alert alert-info mt-4" id="summaryBox" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div class="modal fade" id="imageUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);">
                    <h5 class="modal-title">
                        <i class="fas fa-images me-2"></i>
                        Upload Product Images
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="" class="dropzone" id="productImageDropzone">
                        @csrf
                        <input type="hidden" name="product_id" id="product_id">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Drop images here or click to browse</h5>
                            <p class="text-muted">Support for JPG, PNG, GIF files up to 5MB each</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Complete Modal -->
    <div class="modal fade" id="uploadCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Upload Complete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>Success!</h4>
                    <p class="text-muted">All images have been uploaded successfully!</p>
                    <button class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Products
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/spark-md5@3.0.2/spark-md5.min.js"></script>

    <script>
        let productTable;

        $(document).ready(function() {
            // Initialize DataTable with enhanced configuration
            productTable = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('products.data') }}",
                    error: function(xhr, error, thrown) {
                        console.error('DataTable error:', error);
                        alert('Error loading data. Please refresh the page.');
                    }
                },
                columns: [
                    { data: 'id', name: 'id', className: 'text-center' },
                    { data: 'sku', name: 'sku', className: 'fw-bold' },
                    { data: 'title', name: 'title' },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data) {
                            if (data && data.length > 50) {
                                return data.substring(0, 50) + '...';
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data) {
                            return data ? '$' + parseFloat(data).toFixed(2) : '-';
                        },
                        className: 'text-end'
                    },
                    {
                        data: 'primary_image_path',
                        name: 'Primary Image',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            if (data) {
                                return `<img src="/storage/${data}" alt="Primary Image" class="product-image">`;
                            } else {
                                return `<div class="no-image"><i class="fas fa-image"></i></div>`;
                            }
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString() : '-';
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `
                                <button class="btn btn-success btn-sm upload-images" data-id="${data}" title="Upload Images">
                                    <i class="fas fa-images me-1"></i>
                                    Upload Images
                                </button>
                            `;
                        },
                        className: 'text-center'
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    processing: '<div class="loading-spinner me-2"></div>Processing...',
                    emptyTable: 'No products found',
                    zeroRecords: 'No matching products found'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                drawCallback: function() {
                    $('.fade-in').addClass('fade-in');
                }
            });

            // CSV Upload with enhanced UX
            $('#csvUploadForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Show loading state
                submitBtn.html('<div class="loading-spinner me-2"></div>Processing...').prop('disabled', true);

                let formData = new FormData(this);
                $.ajax({
                    url: "{{ route('products.uploadCsv') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        let cacheKey = res.cacheKey;
                        $('#progressContainer').show();
                        updateProgress(cacheKey);
                    },
                    error: function(xhr) {
                        alert('Error uploading file. Please try again.');
                        submitBtn.html(originalText).prop('disabled', false);
                        $('#progressContainer').hide();
                    }
                });
            });

            function updateProgress(cacheKey) {
                $.get('/products/csv-progress/' + cacheKey, function(data) {
                    let percent = Math.round(data.percent);
                    $('#progressBar').css('width', percent + '%');
                    $('#progressText').text(percent + '%');
                    $('#progressTextInner').text(percent + '%');

                    if (percent < 100) {
                        setTimeout(function() {
                            updateProgress(cacheKey);
                        }, 500);
                    } else {
                        let summary = data.summary;
                        let html = `
                            <div class="stats-grid">
                                <div class="stats-card">
                                    <h3 class="stats-number text-primary">${summary.total || 0}</h3>
                                    <p class="stats-label">Total</p>
                                </div>
                                <div class="stats-card">
                                    <h3 class="stats-number text-success">${summary.imported || 0}</h3>
                                    <p class="stats-label">Imported</p>
                                </div>
                                <div class="stats-card">
                                    <h3 class="stats-number text-warning">${summary.updated || 0}</h3>
                                    <p class="stats-label">Updated</p>
                                </div>
                                <div class="stats-card">
                                    <h3 class="stats-number text-info">${summary.duplicates || 0}</h3>
                                    <p class="stats-label">Duplicates</p>
                                </div>
                                <div class="stats-card">
                                    <h3 class="stats-number text-danger">${summary.invalid || 0}</h3>
                                    <p class="stats-label">Invalid</p>
                                </div>
                                <div class="stats-card">
                                    <h3 class="stats-number text-secondary">${(summary.duplicates || 0) + (summary.invalid || 0)}</h3>
                                    <p class="stats-label">Total Errors</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <button class="btn btn-primary" id="csvBackBtn">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Products
                                </button>
                            </div>
                        `;

                        $('#summaryBox').html(html).show();
                        $('#productsTable').DataTable().ajax.reload();

                        // Reset form and hide progress
                        $('#csv_file').val('');
                        $('#progressContainer').hide();
                        $('#progressBar').css('width', '0%');
                        $('#progressText').text('0%');
                        $('#progressTextInner').text('0%');
                        $('#csvUploadForm button[type="submit"]').html('<i class="fas fa-upload me-2"></i>Upload & Process').prop('disabled', false);

                        $('#csvBackBtn').click(function() {
                            $('#uploadModal').modal('hide');
                            $('#summaryBox').hide();
                        });
                    }
                });
            }

            // MD5 computation function
            function computeFileMD5(file, cb) {
                const chunkSize = 2 * 1024 * 1024;
                const spark = new SparkMD5.ArrayBuffer();
                let fileReader = new FileReader();
                let currentChunk = 0;
                const chunks = Math.ceil(file.size / chunkSize);

                fileReader.onload = function(e) {
                    spark.append(e.target.result);
                    currentChunk++;
                    if (currentChunk < chunks) loadNext();
                    else cb(spark.end());
                };
                fileReader.onerror = function() {
                    cb(null);
                };

                function loadNext() {
                    const start = currentChunk * chunkSize;
                    const end = Math.min(start + chunkSize, file.size);
                    fileReader.readAsArrayBuffer(file.slice(start, end));
                }
                loadNext();
            }

            // Initialize Dropzone for product images
            function initProductDropzone(productId) {
                $('#product_id').val(productId);
                const dzEl = document.querySelector("#productImageDropzone");
                if (dzEl.dropzone) dzEl.dropzone.destroy();

                const myDropzone = new Dropzone("#productImageDropzone", {
                    url: "/images/upload-chunk",
                    chunking: true,
                    forceChunking: true,
                    chunkSize: 2 * 1024 * 1024,
                    parallelChunkUploads: false,
                    retryChunks: true,
                    retryChunksLimit: 3,
                    maxFilesize: 5120,
                    addRemoveLinks: true,
                    autoProcessQueue: true,
                    acceptedFiles: 'image/*',
                    dictDefaultMessage: 'Drop images here or click to browse',
                    dictRemoveFile: 'Remove',
                    dictCancelUpload: 'Cancel',
                    init: function() {
                        this.on("addedfile", function(file) {
                            computeFileMD5(file, function(md5) {
                                file.uploadChecksum = md5;
                            });
                            if (!file.upload) file.upload = {};
                            if (!file.upload.uuid) file.upload.uuid = Dropzone.uuidv4();
                        });

                        this.on("sending", function(file, xhr, formData) {
                            if (file.uploadChecksum) xhr.setRequestHeader('X-Upload-Checksum', file.uploadChecksum);
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                            formData.append('product_id', productId);
                            formData.append('dzuuid', file.upload.uuid);
                        });

                        this.on("success", function(file, response) {
                            if (file.uploadedFinalize) return;
                            file.uploadedFinalize = true;

                            const payload = {
                                dzuuid: file.upload.uuid,
                                filename: file.name,
                                size: file.size,
                                checksum: file.uploadChecksum,
                                product_id: productId
                            };

                            fetch('/images/finalize', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(r => r.json())
                            .then(res => {
                                if (res.ok) {
                                    return fetch('/images/attach', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        body: JSON.stringify({
                                            upload_id: res.upload_id,
                                            product_id: productId
                                        })
                                    });
                                } else {
                                    console.error('Finalize failed:', res);
                                }
                            })
                            .then(r => r && r.json())
                            .then(attachRes => {
                                if (attachRes && attachRes.ok) {
                                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                                        const modal = new bootstrap.Modal(document.getElementById('uploadCompleteModal'));
                                        modal.show();
                                        // Reload the table to show updated images
                                        $('#productsTable').DataTable().ajax.reload();
                                    }
                                } else if (attachRes) {
                                    console.error('Attach failed:', attachRes);
                                }
                            })
                            .catch(err => console.error('Image upload error:', err));
                        });
                    }
                });
            }

            // Open image upload modal
            $(document).on('click', '.upload-images', function() {
                let productId = $(this).data('id');
                initProductDropzone(productId);
                $('#imageUploadModal').modal('show');
            });

            // Add fade-in animation to modals
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('.modal-content').addClass('fade-in');
            });
        });
    </script>
</body>

</html>
