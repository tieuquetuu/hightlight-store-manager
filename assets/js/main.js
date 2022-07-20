let $, 
	HightLightStoreClient;

if (typeof jQuery !== 'undefined') {
	$ = jQuery;
}

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

/*function TrackingPageView() {
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

	/!**!/
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
}*/

function SetupDataTable() {
 	let $tables = $(".store-hightlight-dataTable");
	let dataTableOptions = {
		// rowGroup: true,
		responsive: true,
		// dom: 'Bfrtip',
		/*buttons: [
			'excel'
		]*/
	}
	if ($tables.length) {
		$tables.each(function (tableIndex,tableDom) {
			let $table = $(tableDom);
			let $dataTable = $table.DataTable(dataTableOptions);

			if ($table.hasClass("admin-view")) {
				$table.find("tbody").on("click", "tr", function(e) {
					// $(this).toggleClass('selected')
					let tr = this;
					let $tr = $(tr);
					let rowData = $dataTable.row(tr).data();

					$("#detail-analytics-modal").addClass("show");

					console.log($tr.data())
				});
			}
		})
	}

	// if ($(".store-hightlight-dataTable").length) {
	// 	let dataTableOptions = {
	// 		// rowGroup: true,
	// 		responsive: true,
	// 		// dom: 'Bfrtip',
	// 		buttons: [
	// 			'copy', 'excel', 'pdf'
	// 		]
	// 	}
	// 	$(".store-hightlight-dataTable").DataTable(dataTableOptions);
	// }
}


/**
 * SET UP MODAL JAVASCRIPT
 */

function setupModal() {
	let openModalBtn = $("#myBtn");
	let detailAnalyticsModal = $("#detail-analytics-modal");
	let closeBtn = detailAnalyticsModal.find(".close");

	openModalBtn.on("click", function(e) {
		detailAnalyticsModal.toggleClass("show");
	})

	closeBtn.on("click", function (e) {
		detailAnalyticsModal.removeClass("show");
	})

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == detailAnalyticsModal[0]) {
			detailAnalyticsModal.removeClass("show");
		}
	}
}

// Setup Data Table
$(document).ready(function() {
	SetupDataTable();
	setupModal();
})