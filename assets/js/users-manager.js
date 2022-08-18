function UsersManagerInit() {
    let $table = $("#users-report-table");

    if (!$table.length) {
        return false;
    }

    let { ajaxSource } = $table.data();
    if (!ajaxSource) {
        return false;
    }

    let $dataTable = $table.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        // scrollY: "50vh",
        scrollX: false,
        ajax: ajaxSource,
        columns: [
            {
                className:      'dt-control',
                orderable:      false,
                data:           null,
                defaultContent: '',
            },
            {
                className:      'details-control-author-name',
                orderable:      false,
                data:           null,
                defaultContent: '',
                render: (row, type, data) => {
                    const { display_name, user_email } = data;

                    let name = display_name || user_email

                    return name
                }
            },
            {
                className:      'details-control-luot-xem text-center',
                orderable:      false,
                data:           null,
                defaultContent: '0 lượt',
                render: (row, type, data) => {
                    let totalScreenPageViews = 0;
                    let total_items = [];

                    data.posts.map(post => post.analytics).filter(arr => arr.length > 0).forEach((arr)=>{
                        total_items = total_items.concat(arr)
                    });

                    total_items.forEach((item) => {
                        totalScreenPageViews += parseInt(item.screenPageViews);
                    })

                    return `${totalScreenPageViews} lượt`
                }
            },
            {
                className:      'details-control-click-mua-hang text-center',
                orderable:      false,
                data:           null,
                defaultContent: '0 lượt',
                render: (row, type, data) => {
                    let total_click_buy_product = 0;

                    let total_items = [];

                    data.posts.map(post => post.analytics).filter(arr => arr.length > 0).forEach((arr)=>{
                        total_items = total_items.concat(arr)
                    });

                    total_items.forEach((item) => {
                        if (item.eventName === "click_buy_product") {
                            total_click_buy_product += parseInt(item.eventCount);
                        }
                    })

                    return `${total_click_buy_product} lượt`
                }
            },
            {
                className:      'details-control-click-cua-hang text-center',
                orderable:      false,
                data:           null,
                defaultContent: '0 lượt',
                render: (row, type, data) => {
                    let total_click_view_shop = 0;

                    let total_items = [];

                    data.posts.map(post => post.analytics).filter(arr => arr.length > 0).forEach((arr)=>{
                        total_items = total_items.concat(arr)
                    });

                    total_items.forEach((item) => {
                        if (item.eventName === "click_view_shop") {
                            total_click_view_shop += parseInt(item.eventCount);
                        }
                    })

                    return `${total_click_view_shop} lượt`
                }
            },
            {
                className:      'details-control-thoi-gian-xem text-center',
                orderable:      false,
                data:           null,
                defaultContent: '30 giây',
                render: (row, type, data) => {
                    let totalAverageSessionDuration = 0;

                    let total_items = [];

                    data.posts.map(post => post.analytics).filter(arr => arr.length > 0).forEach((arr)=>{
                        total_items = total_items.concat(arr)
                    });

                    if (total_items.length > 0) {
                        total_items.forEach((item) => {
                            totalAverageSessionDuration += parseFloat(item.averageSessionDuration);
                        })

                        totalAverageSessionDuration = totalAverageSessionDuration / total_items.length;

                        totalAverageSessionDuration = totalAverageSessionDuration.toFixed(1);
                    }

                    return `${totalAverageSessionDuration} giây`
                }
            },
        ],
        columnDefs: [
            {
                "targets": [5],
                "visible": true,
                "searchable": true
            },
        ],
        dom: 'lBrtip',
        buttons: [
            'excel', 'pdf'
        ]
    });

    $table.on('click', 'td.dt-control', function (e) {
        let $tr = $(this).closest('tr');
        let row = $dataTable.row($tr);
        let rowData = row.data()
        let { posts } = rowData;

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            $tr.removeClass('shown');
        } else {
            // Open this row
            row.child(detailAuthorAnalytics(posts)).show();
            $tr.addClass('shown');
        }
    });

    function detailAuthorAnalytics(posts) {

        let rows = '';

        if (!posts || posts.length === 0) {
            rows = `
               <tr colspan="5">
                    <td class="text-center" colspan="8">Không có dữ liệu</td>
                </tr>
            `;
        }

        posts.forEach((post) => {

            let { id, title, status, category, analytics } = post;
            let prod_screenPageViews = 0;
            let prod_click_view_shop = 0;
            let prod_click_buy_product = 0;
            let prod_averageSessionDuration = 0;
            let category_str = category && category.map(cat => cat.name).join()

            if (analytics.length > 0) {
                analytics.forEach((item, itemX) => {
                    // Tính lượt xem
                    prod_screenPageViews += parseInt(item.screenPageViews);

                    // Tính lượt click cửa hàng
                    if (item.eventName === "click_view_shop") {
                        prod_click_view_shop += parseInt(item.eventCount)
                    }

                    // Tính lượt click mua hàng
                    if (item.eventName === "click_buy_product") {
                        prod_click_buy_product += parseInt(item.eventCount)
                    }

                    // Tính lượt thời gian xem trung bình
                    prod_averageSessionDuration += parseFloat(item.averageSessionDuration)

                    // Khi kết thúc vòng lặp
                    if (analytics.length === itemX + 1) {
                        prod_averageSessionDuration = (prod_averageSessionDuration / analytics.length).toFixed(1)
                    }
                })
            }

            rows += `
                <tr>
                    <td>${id}</td>
                    <td>${title}</td>
                    <td>${category_str}</td>
                    <td>${status}</td>
                    <td class="text-center">${prod_screenPageViews} lượt</td>
                    <td class="text-center">${prod_click_buy_product} lượt</td>
                    <td class="text-center">${prod_click_view_shop} lượt</td>
                    <td class="text-right">${prod_averageSessionDuration} giây</td>
                </tr>
             `
        })

        return (
            `<table class="system-report-table-child-row-details display" style="width: 100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Chuyên mục</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center">Lượt xem</th>
                        <th class="text-center">Lượt click mua hàng</th>
                        <th class="text-center">Lượt click cửa hàng</th>
                        <th class="text-center">Thời gian xem trung bình</th>
                    </tr>
                </thead>
                <tbody>
                      ${rows}
                </tbody>
            </table>`
        );
    }

    if ($("#user-filter-user").length) {
        $("#user-filter-user").on("change", function (e) {
            let value = e.currentTarget.value;
            Object.assign({ author: value },$dataTable.ajax.params());
            // $dataTable.ajax.params(newParams);

            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("author", value);
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        })
    }

    if ($("#user-filter-category").length) {
        $("#user-filter-category").on("change", function (e) {
            let categoryId = e.currentTarget.value;
            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("category", categoryId);
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        })
    }

    if ($("#user-filter-domains").length) {
        $("#user-filter-domains").on("change", function (e) {
            let domainName = e.currentTarget.value;
            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("domain", domainName);
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        })
    }

    if ($("#user-filter-daterange").length) {
        let start = moment().subtract(29, 'days');
        let end = moment();

        function cb(start, end) {
            $("#filter-daterange").html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("date_ranges", JSON.stringify({
                start_date: start.format("YYYY-MM-DD"),
                end_date: end.format("YYYY-MM-DD")
            }));
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        }

        $("#user-filter-daterange").daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb)

        cb(start, end);
    }
}

if (typeof $ != undefined) {
    $(document).ready(function() {
        UsersManagerInit();
    })
}