<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dropzone + SparkMD5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/spark-md5@3.0.2/spark-md5.min.js"></script>
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
                    <th>Primary Image</th>
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

    <!-- Upload Complete Modal (reusable) -->
    <div class="modal fade" id="uploadCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Upload Complete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>All images have been uploaded successfully!</p>
                    <button class="btn btn-primary" data-bs-dismiss="modal">Back</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let productTable;

        $(document).ready(function() {
            // DataTable
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
                        data: 'primary_image_path',
                        name: 'Primary Image',
                        render: function(data) {
                            if (data) {
                                return `<img src="/storage/${data}" alt="Primary Image" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">`;
                            } else {
                                return `<img src="/images/no-image.png" alt="No Image" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">`;
                            }
                        }
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

                        // Add Back button
                        html += '<button class="btn btn-primary mt-3" id="csvBackBtn">Back</button>';

                        $('#summaryBox').html(html).show();

                        // Reload DataTable
                        $('#productsTable').DataTable().ajax.reload();

                        // Back button click
                        $('#csvBackBtn').click(function() {
                            $('#uploadModal').modal('hide'); // Close modal
                            $('#summaryBox').hide();
                            $('#progressBar').css('width', '0%').text('0%');
                            $('#csv_file').val('');
                        });
                    }
                });
            }

            // Compute MD5
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

            // Initialize Dropzone for product
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
                    init: function() {
                        this.on("addedfile", function(file) {
                            computeFileMD5(file, function(md5) {
                                file.uploadChecksum = md5;
                            });
                            if (!file.upload) file.upload = {};
                            if (!file.upload.uuid) file.upload.uuid = Dropzone.uuidv4();
                        });

                        this.on("sending", function(file, xhr, formData) {
                            if (file.uploadChecksum) xhr.setRequestHeader('X-Upload-Checksum',
                                file.uploadChecksum);
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
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
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
                                                'X-CSRF-TOKEN': $(
                                                        'meta[name="csrf-token"]')
                                                    .attr('content')
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
                                        if (this.getUploadingFiles().length === 0 && this
                                            .getQueuedFiles().length === 0) {
                                            const modal = new bootstrap.Modal(document
                                                .getElementById('uploadCompleteModal'));
                                            modal.show();
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

            // Open image modal
            $(document).on('click', '.upload-images', function() {
                let productId = $(this).data('id');
                initProductDropzone(productId);
                $('#imageUploadModal').modal('show');
            });
        });
    </script>
</body>

</html>
