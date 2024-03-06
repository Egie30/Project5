function initChosenSelect() {
        var config = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : {allow_single_deselect: true},
            '.chosen-select-no-single' : {disable_search_threshold: 10},
            '.chosen-select-no-results': {no_results_text: "Data tidak ditemukan"},
            '.chosen-select-width'     : {width: "95%"}
        }
          
        for (var selector in config) {
            jQuery(selector).chosen(config[selector]);
        }
    
        jQuery(".chosen-ajax").each(function() {
            var $selector = jQuery(this),
                field = $selector.data("key"),
                text = $selector.data("text"),
                defaultValue = $selector.data("value"),
                settings = {
                    type: "GET",
                    minTermLength: 2,
                    url: $selector.data("ajax"),
                    dataType: "json"
                },
                options = jQuery.extend({}, true, $selector.data(), settings),
                callback = function (response) {
                    var results = [],
                        data = response.data,
                        total = data.length;

                    if (total > 0) {
                        jQuery.each(data, function (index, value) {
                            var items = [];

                            items.push({
                                selected: defaultValue == value[field],
                                value: value[field],
                                text: value[text],
                                html: value[text]
                            });

                            results.push(items);
                        });
                    }

                    return results;
                };
            
            if (options.data == null) {
                options.data = {};
            }

            options.$selector = $selector;

            $selector.ajaxChosen(settings, callback);

            options.success = function(response) {
                var $selector = this.$selector,
                    items = callback != null ? callback(response) : response;

                if (defaultValue && defaultValue in items) {
                    items[defaultValue].selected = true;
                }

                jQuery.each(items, function(i, element) {
                    var value, text,
                        $option = jQuery("<option />");
                    element = element[0];
                    
                    if (typeof element === "string") {
                        value = i;
                        text = element;
                    } else {
                        value = element.value;
                        text = element.text;
                    }

                    $option.attr("value", value).html(text);

                    if (element.selected) {
                        $option.attr("selected", 1);
                    }

                    return $option.appendTo($selector);
                });

                if (items.length) {
                    $selector.trigger("chosen:updated.chosen");
                }
            }

            //$selector.find(".chosen-single").first().html("<span>Searching...</span><div><b></b></div></a>");

            if (defaultValue) {
                options.data[field] = defaultValue;
             
                jQuery.ajax(options);
            }
        });
}

(function($) {
    $(document).ready(function() {
        initChosenSelect();
    });
})(jQuery);