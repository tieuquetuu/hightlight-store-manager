// let $,
//     HightLightStoreClient;
//
// if (typeof jQuery !== 'undefined') {
//     $ = jQuery;
// }

if (typeof hightlight_client_object !== 'undefined') {
    HightLightStoreClient = hightlight_client_object;
}

function is_hl_product_page() {
    return HightLightStoreClient?.is_hightlight_product === '1';
}


/**
 * @name:
 * @description: Gửi Thông tin trang mỗi lần load page
 *
 */

function TrackingPageView() {
    gtag('event', 'page_view', {
        // 'event_category': "",
        'event_label': "Xem trang",
        ...HightLightStoreClient
    });
}


function TrackingViewProduct() {

    if (is_hl_product_page()) {
        console.log("Đây là trang sản phẩm")

        gtag("event", "view_product_item", {
            "Tên miền": HightLightStoreClient?.hostname,
            "Đường dẫn": location.pathname,
            "Đường dẫn chi tiết": location.href,
            'event_label': "Xem trang sản phẩm",
            ...HightLightStoreClient
        })

    };

    /**/
}


function TrackingClickGoToStore() {
    $('.hl-cua-hang').on('click', function(event){
        console.log("User click vào button cửa hàng")
        gtag("event", "click_view_shop", {
            "Tên miền": location.hostname,
            "Đường dẫn": location.pathname,
            "Đường dẫn chi tiết": location.href,
            'event_label': "Nhấn nút cửa hàng",
            ...HightLightStoreClient
        });
    })
}


function TrackingClickBuyProduct() {
    $('.hl-mua-hang').on('click', function(event){
        console.log("User click vào button mua hàng")
        gtag("event", "click_buy_product", {
            "Tên miền": location.hostname,
            "Đường dẫn": location.pathname,
            "Đường dẫn chi tiết": location.href,
            'event_label': "Nhấn nút mua hàng",
            ...HightLightStoreClient
        });
    })
}


function SetupTracking() {
    TrackingClickGoToStore()
    TrackingClickBuyProduct()
    TrackingViewProduct()
    TrackingPageView()
}

// Setup tracking if gtag isset
if (typeof gtag !== 'undefined') {
    $(document).ready(function() {
        SetupTracking();
    })
}