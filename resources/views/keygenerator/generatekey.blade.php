@extends('layout.apikeylayout')

@section('title','Key')



@section('main-content')
<div class="flex">
    <!-- API Key Table -->
    <div class="w-full max-w-full p-6 ml-0 bg-white rounded-lg shadow-md main-content table_key">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Generated API Keys</h2>
        <table class="min-w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">App Name</th>
                    <th class="border border-gray-300 p-2">Directorate</th>
                    <th class="border border-gray-300 p-2">IP Address</th>
                    <th class="border border-gray-300 p-2">API Key</th>
                    <th class="border border-gray-300 p-2">Action</th>
                </tr>
            </thead>
            <tbody id="apiKeyTableBody">
                <!-- Dynamic Rows will be appended here -->
            </tbody>
        </table>
        <button
            type="javascript"
            id="addnewkey"
            class=" px-4 py-2 mt-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
            Add New Key
        </button>
    </div>

    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md main-content generate_key ">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">API Key Generator</h2>

        <form id="apiKeyForm">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">App Name</label>
                <input type="hidden" name="id" id='api_id'>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
            </div>

            <div class="mb-4">
                <label for="directorate" class="block text-sm font-medium text-gray-700">Directorate</label>
                <input
                    type="text"
                    name="directorate"
                    id="directorate"
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
            </div>

            <div class="mb-4">
                <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
                <input
                    type="text"
                    name="ip_address"
                    id="ip_address"
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
            </div>

            <div class="mb-4">
                <label for="key" class="block text-sm font-medium text-gray-700">API Key</label>
                <div class="flex mt-2">
                    <input
                        type="text"
                        name="apikey"
                        id="key"
                        readonly
                        class="flex-1 p-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Click Generate to get key" />
                    <button
                        type="button"
                        id="generateButton"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-r-md hover:bg-gray-400">
                        Generate
                    </button>
                </div>
            </div>

            <button
                type="submit"
                id="submitButton"
                class="w-full px-4 py-2 mt-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                Store Key
            </button>
            <button
                type="javascript"
                id="keytablebtn"
                class="w-full px-4 py-2 mt-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                Back Key
            </button>
        </form>

        <div id="message" class="mt-4 text-center"></div>
    </div>
</div>




@endsection

@section('script')
<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }

        });



        $('#keytablebtn').click();

        // Load API keys on page load

        loadTable();


        // Generate API Key on Button Click
        $('#generateButton').click(function() {
            const generatedKey = [...Array(40)]
                .map(() => Math.random().toString(36)[2])
                .join('');
            $('#key').val(generatedKey);
        });




        // Submit Form using AJAX
        $('#apiKeyForm').submit(function(e) {
            e.preventDefault(); // Prevent the default form submission

            const formData = {
                id: $('#api_id').val(),
                name: $('#name').val(),
                directorate: $('#directorate').val(),
                ip_address: $('#ip_address').val(),
                apikey: $('#key').val()
            };


            $.ajax({
                url: "{{ route('master.key.store') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#message').html('<p class="text-green-500">' + response.success + '</p>');

                    $('#apiKeyForm')[0].reset(); // Reset the form

                    loadTable();

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred.';
                    $('#message').html('<p class="text-red-500">' + errorMessage + '</p>');
                }
            });



        });


        // api key edit 

        $('body').on('click', '.editbutton', function() {
            var id = $(this).data('id');

            key_id = id;
            $('.editbutton').attr('disabled', true);


            $.ajax({
                method: "get",
                url: "{{ route('master.key.edit') }}",
                data: {
                    id: key_id
                },
                success: function(response) {

                    $('.main-content').hide();
                    $('.generate_key').show();
                    $('#api_id').val('');
                    $('#api_id').val(response.id);

                    $('#name').val('');
                    $('#name').val(response.name);
                    $('#directorate').val('');
                    $('#directorate').val(response.directorate);
                    $('#ip_address').val('');
                    $('#ip_address').val(response.ip_address);

                    $('#key').val('');
                    $('#key').val(response.key);

                    $('#submitButton').html('Update Key');

                },
                error: function(error) {



                }
            });


        });


        $('body').on('click', '.blockapibtn', function() {

            var id = $(this).data('id');


            Swal.fire({
                title: "Do you want to Lock this API key ?",
                showDenyButton: false,
                showCancelButton: true,
                confirmButtonText: "Save",
                denyButtonText: `Don't save`
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {

                    $.ajax({
                        type: "POST",
                        url: "{{ route('master.key.revoke') }}",
                        data: {
                            id: id
                        },

                        success: function(response) {
                            Swal.fire(response.success, "success", 'success')

                            loadTable();

                        }
                    });

                } else if (result.isDenied) {
                    Swal.fire("Changes are not saved", "", "info");
                }
            });
        })


        // Copy API Key when Input Field is Clicked
        $('#key').click(function() {
            const key = $(this).val();
            if (key) {
                navigator.clipboard.writeText(key).then(() => {
                    alert('API key copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            }
        });


    });

    $('#addnewkey').click(function(e) {
        e.preventDefault();
        $('.main-content').hide();
        $('.generate_key').show();
    });
    $('#keytablebtn').click(function(e) {
        e.preventDefault();
        $('.main-content').hide();
        $('.table_key').show();
        $('#name').val('');
        $('#directorate').val('');
        $('#ip_address').val('');

        $('#key').val('');

        $('#submitButton').html('Store Key');

    });
    // Define a function to load the table
    function loadTable() {
        $.ajax({
            url: "{{ route('master.key.index') }}", // Adjust to your route
            type: 'GET',
            success: function(data) {
                // Clear existing rows to prevent duplication
                $('#apiKeyTableBody').empty();

                // Populate the table with new data
                data.forEach(function(apiKey) {
                    var activeIcon = apiKey.is_active !== 1 ?
                        `<box-icon name='lock' title='Open the API key' color='#f50909'></box-icon>` :
                        `<box-icon name='lock-open' title='Lock the API key' color='#09f545'></box-icon>`;

                    $('#apiKeyTableBody').append(`
                    <tr>
                        <td class="border border-gray-300 p-2">${apiKey.name}</td>
                        <td class="border border-gray-300 p-2">${apiKey.directorate}</td>
                        <td class="border border-gray-300 p-2">${apiKey.ip_address}</td>
                        <td class="border border-gray-300 p-2">${apiKey.key}</td>
                        <td class="border border-gray-300 p-2">
                            <a href="javascript:void(0)" class="editbutton" data-id="${apiKey.id}">
                                <box-icon name="edit" color="#01a9e4"></box-icon>
                            </a>
                            <a href="javascript:void(0)" class="blockapibtn" data-id="${apiKey.id}">
                                ${activeIcon}
                            </a>
                        </td>
                    </tr>
                `);
                });
            },
            error: function(xhr, status, error) {
                console.error("Failed to load data:", error);
                Swal.fire({
                    title: "Error",
                    text: "Failed to load API keys. Please try again later.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }
</script>


@endsection