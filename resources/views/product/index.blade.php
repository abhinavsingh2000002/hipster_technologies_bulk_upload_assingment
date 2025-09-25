<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h1>Products</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload CSV</button>

        <table id="productsTable" class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- CSV Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Upload CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="csvUploadForm" enctype="multipart/form-data">@csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV</label>
                            <input type="file" class="form-control" name="csv_file" id="csv_file" required>
                        </div>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </form>

                    <div class="progress mt-3" style="height:25px; display:none;">
                        <div class="progress-bar" role="progressbar" style="width:0%" id="progressBar">0%</div>
                    </div>

                    <div class="alert alert-info mt-3" id="summaryBox" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div class="modal fade" id="imageUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Upload Product Images</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="" class="dropzone" id="productImageDropzone">
                        @csrf
                        <input type="hidden" name="product_id" id="product_id">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let productTable;
        let dz;

        $(document).ready(function() {
            // DataTable with server-side rendering
            productTable = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('products.data') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'id',
                        render: function(data) {
                            return `<button class="btn btn-sm btn-success upload-images" data-id="${data}">Upload Images</button>`;
                        }
                    }
                ]
            });

            // CSV Upload
            $('#csvUploadForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    url: "{{ route('products.uploadCsv') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        let cacheKey = res.cacheKey;
                        $('.progress').show();
                        updateProgress(cacheKey);
                    }
                });
            });

            function updateProgress(cacheKey) {
                $.get('/products/csv-progress/' + cacheKey, function(data) {
                    let percent = data.percent;
                    $('#progressBar').css('width', percent + '%').text(percent + '%');

                    if (percent < 100) {
                        setTimeout(function() {
                            updateProgress(cacheKey);
                        }, 500);
                    } else {
                        let summary = data.summary;
                        let html = '<h5>CSV Import Summary</h5><ul class="mb-0">';
                        html += '<li>Total: ' + summary.total + '</li>';
                        html += '<li>Imported: ' + summary.imported + '</li>';
                        html += '<li>Updated: ' + summary.updated + '</li>';
                        html += '<li>Invalid: ' + summary.invalid + '</li>';
                        html += '<li>Duplicates: ' + summary.duplicates + '</li></ul>';
                        $('#summaryBox').html(html).show();
                        $('#productsTable').DataTable().ajax.reload();
                    }
                });
            }

        });
    </script>
</body>

</html>
