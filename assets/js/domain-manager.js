function DomainManagerInit() {
    let $table = $("#domain-report-table");

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
                className:      'details-control-domain',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    return data.hostName
                }
            },
            {
                className:      'text-center details-control-luot-xem',
                // orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let totalScreenPageViews = 0;

                    let { analytics } = data;

                    if (!analytics || analytics.length <= 0) {
                        return totalScreenPageViews
                    }

                    for (let i = 0;i < analytics.length;i++) {
                        let analyticsItem = analytics[i];
                        totalScreenPageViews += parseInt(analyticsItem?.screenPageViews);
                    }

                    return `${totalScreenPageViews} lượt xem`;
                }
            },
            {
                className:      'text-center details-control-luot-click-cua-hang',
                // orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let totalClick = 0;

                    let { analytics } = data;

                    let analytics_click_view_shop = analytics.filter(obj => obj.eventName === "click_view_shop");

                    if (!analytics || analytics.length <= 0 || !analytics_click_view_shop) {
                        return totalClick
                    }

                    for (let i = 0;i < analytics_click_view_shop.length;i++) {
                        let analyticsItem = analytics_click_view_shop[i];
                        totalClick += parseInt(analyticsItem?.eventCount);
                    }

                    return `${totalClick} lượt`
                }
            },
            {
                className:      'text-center details-control-luot-click-mua-hang',
                // orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let totalClick = 0;

                    let { analytics } = data;

                    let analytics_click_buy_product = analytics.filter(obj => obj.eventName === "click_buy_product");

                    if (!analytics || analytics.length <= 0 || !analytics_click_buy_product) {
                        return totalClick
                    }

                    for (let i = 0;i < analytics_click_buy_product.length;i++) {
                        let analyticsItem = analytics_click_buy_product[i];
                        totalClick += parseInt(analyticsItem?.eventCount);
                    }

                    return `${totalClick} lượt`
                }
            },
            {
                className:      'text-right details-control-thoi-gian-xem-trung-binh',
                // orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let averageSessionDuration = 0;

                    let { analytics } = data;
                    if (!analytics || analytics.length <= 0) {
                        return averageSessionDuration
                    }

                    for (let i = 0;i < analytics.length;i++) {
                        let analyticsItem = analytics[i];
                        averageSessionDuration += parseFloat(analyticsItem?.averageSessionDuration);
                    }

                    averageSessionDuration = averageSessionDuration / analytics.length;

                    return `${averageSessionDuration.toFixed(1)} giây`
                }
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
        let { analytics, hostName } = rowData;

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            $tr.removeClass('shown');
        } else {
            // Open this row
            row.child(detailDomainAnalytics(rowData)).show();
            $tr.addClass('shown');
        }
    });

    function detailDomainAnalytics(data) {

        let { products, analytics } = data;

        let rows = '';

        if (products.length == 0) {
            rows = `
               <tr colspan="5">
                    <td class="text-center" colspan="8">Không có dữ liệu</td>
                </tr>
            `;
        } else {
            products.forEach((post) => {

                let { id, title, status, category, slug } = post;
                let prod_screenPageViews = 0;
                let prod_click_view_shop = 0;
                let prod_click_buy_product = 0;
                let prod_averageSessionDuration = 0;
                let category_str = category && category.map(cat => cat.name).join()
                let productAnalytics = analytics.filter(obj => {
                    let pagePath = obj.pagePath.replace("/product/", "").replace("/nha-dat/", "").replace("/", "");
                    return pagePath === slug;
                });

                if (productAnalytics.length > 0) {
                    productAnalytics.forEach((item, itemX) => {
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
                }
            })
        }

        return (
            `<table class="domain-report-table-child-row-details display" style="width: 100%">
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
        )
    }

    if ($("#domain-filter-user").length) {
        $("#domain-filter-user").on("change", function (e) {
            let value = e.currentTarget.value;
            Object.assign({ author: value },$dataTable.ajax.params());
            // $dataTable.ajax.params(newParams);

            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("author", value);
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        })
    }

    if ($("#domain-filter-category").length) {
        $("#domain-filter-category").on("change", function (e) {
            let categoryId = e.currentTarget.value;
            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("category", categoryId);
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        })
    }

    if ($("#domain-filter-daterange").length) {
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

        $("#domain-filter-daterange").daterangepicker({
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

        // cb(start, end);
    }
}

if (typeof $ != undefined) {
    $(document).ready(function() {
        DomainManagerInit();
    })
}