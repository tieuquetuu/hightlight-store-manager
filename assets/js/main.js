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

function setupManagementDataTable() {
	let $tables = $(".store-hightlight-management-dataTable");
	let dataTableOptions = {
		responsive: true,
		processing: true,
		serverSide: true,
	}

	if ($tables.length) {
		$tables.each(function (tableIndex,tableDom) {
			let $table = $(tableDom);
			let { ajaxSource } = $table.data();
			if (ajaxSource) {
				dataTableOptions.ajax = ajaxSource;
			}
			let $dataTable = $table.DataTable(dataTableOptions);
		})
	}
}

function systemManagementTable() {
	let $table = $("#system-management-dataTable");
	let { ajaxSource } = $table.data();

	$table.DataTable({
		processing: true,
		serverSide: true,
		ajax: ajaxSource,
		columns: [
			{
				data: "id",
			},
			{
				data: "title",
			},
			{
				data: "author.display_name",
			},
			{
				render: (index, text, row) => {
					let tongLuotXem = 0
					for (let i = 0; i < row.analytics_data.length;i++) {
						tongLuotXem += parseInt(row.analytics_data[i].screenPageViews);
					}
					return tongLuotXem
				}
			},
			{
				render: (index, text, row) => {
					let tongClickCuaHang = 0
					for (let i = 0; i < row.analytics_data.length;i++) {
						if (row.analytics_data[i].eventName === "click_buy_product") {
							tongClickCuaHang += parseInt(row.analytics_data[i].eventCount);
						}
					}
					return tongClickCuaHang
				}
			},
			{
				render: (index, text, row) => {
					let tongClickMuaHang = 0
					for (let i = 0; i < row.analytics_data.length;i++) {
						if (row.analytics_data[i].eventName === "click_view_shop") {
							tongClickMuaHang += parseInt(row.analytics_data[i].eventCount);
						}
					}
					return tongClickMuaHang
				}
			},
			{
				render: (index, text, row) => {
					let tongThoiGianTrungBinh = 0
					for (let i = 0; i < row.analytics_data.length;i++) {
						let count = parseFloat(row.analytics_data[i].averageSessionDuration);

						tongThoiGianTrungBinh += count;
					}

					if (row.analytics_data.length > 0) {
						tongThoiGianTrungBinh = tongThoiGianTrungBinh / row.analytics_data.length;
					}

					return tongThoiGianTrungBinh
				}
			},
			{
				data: "status",
			}
		]
	});
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

//Multi CheckBox
var defaultMultiCheckBoxOption = { width: '220px', defaultText: 'Select Below', height: '200px' };

	jQuery.fn.extend({
		CreateMultiCheckBox: function (options) {

			var localOption = {};
			localOption.width = (options != null && options.width != null && options.width != undefined) ? options.width : defaultMultiCheckBoxOption.width;
			localOption.defaultText = (options != null && options.defaultText != null && options.defaultText != undefined) ? options.defaultText : defaultMultiCheckBoxOption.defaultText;
			localOption.height = (options != null && options.height != null && options.height != undefined) ? options.height : defaultMultiCheckBoxOption.height;

			this.hide();
			this.attr("multiple", "multiple");
			var divSel = $("<div class='MultiCheckBox'>" + localOption.defaultText + "<span class='k-icon k-i-arrow-60-down'><svg aria-hidden='true' focusable='false' data-prefix='fas' data-icon='sort-down' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512' class='svg-inline--fa fa-sort-down fa-w-10 fa-2x'><path fill='currentColor' d='M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41z' class=''></path></svg></span></div>").insertBefore(this);
			divSel.css({ "width": localOption.width });

			var detail = $("<div class='MultiCheckBoxDetail'><div class='MultiCheckBoxDetailHeader'><input type='checkbox' class='mulinput' value='-1982' /><div>Select All</div></div><div class='MultiCheckBoxDetailBody'></div></div>").insertAfter(divSel);
			detail.css({ "width": parseInt(options.width) + 10, "max-height": localOption.height });
			var multiCheckBoxDetailBody = detail.find(".MultiCheckBoxDetailBody");

			this.find("option").each(function () {
				var val = $(this).attr("value");

				if (val == undefined)
					val = '';

				multiCheckBoxDetailBody.append("<div class='cont'><div><input type='checkbox' class='mulinput' value='" + val + "' /></div><div>" + $(this).text() + "</div></div>");
			});

			multiCheckBoxDetailBody.css("max-height", (parseInt($(".MultiCheckBoxDetail").css("max-height")) - 28) + "px");
		},
		UpdateSelect: function () {
			var arr = [];

			this.prev().find(".mulinput:checked").each(function () {
				arr.push($(this).val());
			});

			this.val(arr);
		},
	});
function setupMultiCheckBox() {
	

	$(document).on("click", ".MultiCheckBox", function () {
		var detail = $(this).next();
		detail.show();
	});

	$(document).on("click", ".MultiCheckBoxDetailHeader input", function (e) {
		e.stopPropagation();
		var hc = $(this).prop("checked");
		$(this).closest(".MultiCheckBoxDetail").find(".MultiCheckBoxDetailBody input").prop("checked", hc);
		$(this).closest(".MultiCheckBoxDetail").next().UpdateSelect();
	});

	$(document).on("click", ".MultiCheckBoxDetailHeader", function (e) {
		var inp = $(this).find("input");
		var chk = inp.prop("checked");
		inp.prop("checked", !chk);
		$(this).closest(".MultiCheckBoxDetail").find(".MultiCheckBoxDetailBody input").prop("checked", !chk);
		$(this).closest(".MultiCheckBoxDetail").next().UpdateSelect();
	});

	$(document).on("click", ".MultiCheckBoxDetail .cont input", function (e) {
		e.stopPropagation();
		$(this).closest(".MultiCheckBoxDetail").next().UpdateSelect();

		var val = ($(".MultiCheckBoxDetailBody input:checked").length == $(".MultiCheckBoxDetailBody input").length)
		$(".MultiCheckBoxDetailHeader input").prop("checked", val);
	});

	$(document).on("click", ".MultiCheckBoxDetail .cont", function (e) {
		var inp = $(this).find("input");
		var chk = inp.prop("checked");
		inp.prop("checked", !chk);

		var multiCheckBoxDetail = $(this).closest(".MultiCheckBoxDetail");
		var multiCheckBoxDetailBody = $(this).closest(".MultiCheckBoxDetailBody");
		multiCheckBoxDetail.next().UpdateSelect();

		var val = ($(".MultiCheckBoxDetailBody input:checked").length == $(".MultiCheckBoxDetailBody input").length)
		$(".MultiCheckBoxDetailHeader input").prop("checked", val);
	});

	$(document).mouseup(function (e) {
		var container = $(".MultiCheckBoxDetail");
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.hide();
		}
	});

	$(".MultiCheckBox.MultiCheckBox-LoaiSanPham").CreateMultiCheckBox({ width: '230px', defaultText : 'Loại Sản Phẩm', height:'250px' });
	$(".MultiCheckBox.MultiCheckBox-LoaiDichVu").CreateMultiCheckBox({ width: '230px', defaultText : 'Loại Dịch Vụ', height:'250px' });
}
/**
	 * Login
	 */
 $('.form-login-store-hightlight-manager').on('submit', function(e) {
	e.preventDefault();
	$form = $(this);
	$form.find('[type="submit"]').append('<i class="ion-loop spin icon icon-right"></i>');
	$form.find('.alert').remove();
	$.ajax({
		type: 'POST',
		url: ajax.ajax_url,
		data: $form.serialize(),
		success: function( data, textStatus, jqXHR ) {
			$form.find('[type="submit"]').find('.icon').remove();
			if( data.status == true ) {
				$form.append('<div class="alert alert-success">' + data.message + '</div>');
				$form.find('.form-control').val('');
					window.location.href = '/quan-ly-du-lieu-san-pham/du-lieu-he-thong';
			} else {
				$form.append('<div class="alert alert-danger">' + data.message + '</div>');
			}
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			alert( errorThrown );
		}
	});
});
// Setup Data Table
$(document).ready(function() {
	SetupDataTable();
	setupManagementDataTable();
	systemManagementTable();
	setupModal();
	setupMultiCheckBox();
})