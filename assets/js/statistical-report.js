function StatisticalTableInit() {
    let $table = $("#products-table-analytics");

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
                className:      'details-control-id',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    return data?.id
                }
            },
            {
                className:      'details-control-title',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    return data?.title
                }
            },
            {
                className:      'details-control-category',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let categoryName = data?.category.map(obj => obj.name);

                    categoryName = categoryName.join(",");

                    return categoryName
                }
            },
            {
                className:      'text-center details-control-luot-xem',
                orderable:      false,
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
                orderable:      false,
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
                orderable:      false,
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
                orderable:      false,
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
            {
                className:      'details-control-status',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    return data?.status
                }
            },
        ],
        dom: 'lBfrtip',
        buttons: [
            'excel', 'pdf'
        ],

    });

    $table.on('click', 'td.dt-control', function (e) {
        let $tr = $(this).closest('tr');
        let row = $dataTable.row($tr);
        let rowData = row.data()
        let { analytics } = rowData;

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            $tr.removeClass('shown');
        } else {
            // Open this row
            row.child(analyticsFormat(analytics)).show();
            // console.log(analyticsFormat(analytics))
            $tr.addClass('shown');
        }
    });

    if ($("#report-filter-daterange").length) {
        let start = moment().subtract(29, 'days');
        let end = moment();

        function cb(start, end) {
            $("#report-filter-daterange").html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

            let initialUrl = $dataTable.ajax.url();
            let newUrl = new URL(initialUrl);
            newUrl.searchParams.set("date_ranges", JSON.stringify({
                start_date: start.format("YYYY-MM-DD"),
                end_date: end.format("YYYY-MM-DD")
            }));
            $dataTable.ajax.url(newUrl.href).ajax.reload();
        }

        $("#report-filter-daterange").daterangepicker({
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

    function analyticsFormat(dataAnalytics) {
        // `d` is the original data object for the row
        let rows = '';

        if (dataAnalytics.length == 0) {
            rows = `
               <tr colspan="5">
                    <td class="text-center" colspan="5">Không có dữ liệu</td>
                </tr>
            `;
        } else {

            let data = {}

            let dataByHostNames = {};

            dataAnalytics.forEach(function(obj, count){
                let { eventName, eventCount, screenPageViews, hostName,averageSessionDuration  } = obj;
                let hostNameKey = hostName;

                if (!(hostNameKey in dataByHostNames)) {
                    dataByHostNames[hostNameKey] = {
                        "hostName": hostName,
                        "click_view_shop" : 0,
                        "click_buy_product" : 0,
                        "screenPageViews" : 0,
                        "averageSessionDuration" : 0,
                    };
                }

                if (eventName === "click_buy_product") {
                    dataByHostNames[hostNameKey]["click_buy_product"] += parseInt(eventCount);
                }
                if (eventName === "click_view_shop") {
                    dataByHostNames[hostNameKey]["click_view_shop"] += parseInt(eventCount);
                }
                dataByHostNames[hostNameKey]["screenPageViews"] += parseInt(screenPageViews);
                dataByHostNames[hostNameKey]["averageSessionDuration"] += parseFloat(averageSessionDuration);
            })

            // Map lại thời gian xem trung bình


            Object.values(dataByHostNames).forEach(function(d) {

                let totalItems = dataAnalytics.filter(obj => obj.hostName === d.hostName).length;

                rows += `
                    <tr>
                        <td>${d.hostName}</td>
                        <td class="text-center">${d.screenPageViews} lượt xem</td>
                        <td class="text-center">${d.click_view_shop} lượt</td>
                        <td class="text-center">${d.click_buy_product} lượt</td>
                        <td class="text-right">${(d.averageSessionDuration / totalItems).toFixed(1)} giây</td>
                    </tr>
                 `
            });
        }

        return (
            `<table class="system-report-table-child-row-details display" style="width: 100%">
                <thead>
                    <tr>
                        <th>Tên miền</th>
                        <th class="text-center">Lượt xem</th>
                        <th class="text-center">Lượt click cửa hàng</th>
                        <th class="text-center">Lượt click mua hàng</th>
                        <th class="text-right">Thời gian xem trung bình</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>`
        );
    }
}

function ManagerProductTableInit() {
    let $table = $("#product-table-manager");

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
        select: true,
        scrollX: false,
        ajax: ajaxSource,
        columns: [
            {
                className:      'dt-control-id',
                orderable:      false,
                data:           null,
                defaultContent: '',
                render: (row, type, data) => {
                    let { id } = data;
                    return id
                }
            },
            {
                className:      'dt-control-title',
                orderable:      false,
                data:           null,
                defaultContent: '',
                render: (row, type, data) => {
                    let { title } = data;
                    return title
                }
            },
            {
                className:      'dt-control-end-day',
                orderable:      false,
                data:           null,
                defaultContent: '',
                render: (row, type, data) => {
                    let { end_day } = data;

                    if (!end_day) {
                        return "Không xác định";
                    }

                    return moment(end_day, "YYYYMMDD").calendar()
                }
            },
            {
                className:      'dt-control-status',
                orderable:      false,
                data:           null,
                defaultContent: 'Trạng thái',
                render: (row, type, data) => {
                    let { status } = data;
                    return status
                }

            },
            {
                className:      'dt-control-luot-xem',
                orderable:      false,
                data:           null,
                defaultContent: '0 lượt',
                render: (row, type, data) => {
                    let totalScreenPageViews = 0;

                    let { analytics } = data;

                    if (!analytics || analytics.length <= 0) {
                        return `${totalScreenPageViews} lượt`;
                    }

                    for (let i = 0;i < analytics.length;i++) {
                        let analyticsItem = analytics[i];
                        totalScreenPageViews += parseInt(analyticsItem?.screenPageViews);
                    }

                    return `${totalScreenPageViews} lượt`;
                }
            },
            {
                className:      'text-center details-control-luot-click-mua-hang',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let totalClick = 0;

                    let { analytics } = data;

                    let analytics_click_buy_product = analytics.filter(obj => obj.eventName === "click_buy_product");

                    if (!analytics || analytics.length <= 0 || !analytics_click_buy_product) {
                        return `${totalClick} lượt`
                    }

                    for (let i = 0;i < analytics_click_buy_product.length;i++) {
                        let analyticsItem = analytics_click_buy_product[i];
                        totalClick += parseInt(analyticsItem?.eventCount);
                    }

                    return `${totalClick} lượt`
                }
            },
            {
                className:      'text-center details-control-luot-click-cua-hang',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let totalClick = 0;

                    let { analytics } = data;

                    let analytics_click_view_shop = analytics.filter(obj => obj.eventName === "click_view_shop");

                    if (!analytics || analytics.length <= 0 || !analytics_click_view_shop) {
                        return `${totalClick} lượt`
                    }

                    for (let i = 0;i < analytics_click_view_shop.length;i++) {
                        let analyticsItem = analytics_click_view_shop[i];
                        totalClick += parseInt(analyticsItem?.eventCount);
                    }

                    return `${totalClick} lượt`
                }
            },
            {
                className:      'text-center details-control-thoi-gian-xem-trung-binh',
                orderable:      false,
                data:           null,
                defaultContent: 'không có dữ liệu',
                render: (row, type, data) => {
                    let averageSessionDuration = 0;

                    let { analytics } = data;
                    if (!analytics || analytics.length <= 0) {
                        return `${averageSessionDuration} giây`
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
        ],
        initComplete: function(settings, json) {
            let totalProducts = json.recordsTotal;
            $("#total-products .total-card-count").text(totalProducts)
        }
    });

    let $detailTable = $("#detail-product-analytics");
    let $detailDataTable = $detailTable.dataTable().api();
    let initialUrl = $detailDataTable.ajax.url();

    // Tab preview sản phẩm
    let $productPreviewWrap = $("#product-preview-wrap");
    let $productPreviewId = $productPreviewWrap.find(".product-preview-id span");
    let $productPreviewTitle = $productPreviewWrap.find(".product-preview__title");
    let $productPreviewPrice = $productPreviewWrap.find(".product-preview__price span");
    let $productPreviewUpdated = $productPreviewWrap.find(".product-preview__updated span");
    let $productPreviewGallery = $productPreviewWrap.find(".product-preview__gallery");
    let $productPreviewContent = $productPreviewWrap.find(".product-preview__content-inner");
    let $productPreviewEditBtn = $productPreviewWrap.find(".product-preview__edit-btn");
    let $productPreviewDeleteBtn = $productPreviewWrap.find(".product-preview__delete-btn");

    $dataTable.on("select", function(e, dt, type, indexes) {
        let rowData = $dataTable.row(indexes).data();
        let { id: value, product, gallery, price } = rowData;

        // Load lại bảng số liệu chi tiết
        let newUrl = new URL(initialUrl);
        newUrl.searchParams.set("product_id", value);
        $("#detail-product-analytics-table .table-heading .product-analytics-id").text(`Mã sản phẩm: ${value}`)
        $detailDataTable.ajax.url(newUrl.href).ajax.reload();

        // Load lại phần preview sản phẩm
        if (!$productPreviewWrap.hasClass("shown")) {
            $productPreviewWrap.addClass("shown");
        }

        let { site_url } = hightlight_client_object || {};
        let { ID, post_title, post_content, post_modified } = product;

        // Cập nhật vào phần preview
        $productPreviewTitle.text(post_title)
        $productPreviewId.text(ID)
        $productPreviewContent.html(post_content)
        $productPreviewUpdated.text(post_modified)
        $productPreviewPrice.text(parseInt(price).toLocaleString('it-IT', {style : 'currency', currency : 'VND'}))
        $productPreviewEditBtn.attr("href", `${site_url}/nguoi-dung/dang-tin?action=edit&id=${ID}`)
        $productPreviewDeleteBtn.attr("href", `${site_url}/nguoi-dung/danh-sach-tin-dang?action=delete&id=${ID}`)

        let galleryItems = ``;
        gallery.forEach((item) => {
            galleryItems += (`
                <div class="product-preview__gallery-item">
                    <div class="product-preview__gallery-item__image">
                        <img src="${item.src}" alt="">
                    </div>
                </div>
            `)
        })
        $productPreviewGallery.html(galleryItems)

    })

    $dataTable.on("deselect", function(e, dt, type, indexes) {
        let countSelected = $dataTable.rows({ selected: true }).count();

        $productPreviewWrap.removeClass("shown");
    })
}

function DetailReportTableInit() {
    let $table = $("#detail-product-analytics");
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
        scrollX: false,
        ajax: ajaxSource,
        columns: [
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
        ],
        initComplete: function(settings, json) {
            let { extra_data } = json;
            if (!extra_data) {
                return false
            }

            let { clickByProduct, clickViewShop, screenPageViews } = extra_data;

            $("#total-screen-page-views .total-card-count").text(`${screenPageViews} Lượt`);
            $("#total-click-view-shop .total-card-count").text(`${clickViewShop} Lượt`);
            $("#total-click-buy-product .total-card-count").text(`${clickByProduct} Lượt`);
        }
    })
}

if (typeof $ != undefined) {
    $(document).ready(function() {
        // StatisticalTableInit();
        DetailReportTableInit();
        ManagerProductTableInit();
    })
}