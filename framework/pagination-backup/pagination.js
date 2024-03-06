function paginationLoadPage(element) {
	var $container = jQuery(element).closest(".pagination-container").parent(), // Catch the pagination container
		uriQuery = jQuery(element).attr("href"),
		baseUri = jQuery(element).attr("data-base-url"),
		containerId = $container.attr("id");

	if (!containerId) {
		containerId = Math.random();

		while(jQuery("#" + containerId).length > 0)  {
			containerId = Math.random();
		}

		$container.attr("id", containerId);
	}

	getContent(containerId, baseUri + uriQuery);

	return false;
}