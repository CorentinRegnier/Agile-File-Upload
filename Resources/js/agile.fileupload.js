window.Agile = window.Agile || {};

(function ($, Agile) {
    "use strict";

    Agile.fileUpload = {
        setup: function ($c, options) {
            if (!Array.prototype.indexOf) {
                Array.prototype.indexOf = function (val) {
                    return jQuery.inArray(val, this);
                };
            }

            var settings = $.extend({
                    multiple: false,
                    ajax: true,
                    token: null,
                    unknown_error_message: "Sorry, an error has occured",
                    icon_classes: {},
                    pending_uploads_label: "Loading..."
                }, options),
                pendingUploads = 0,
                submitRequested = false,
                $id = $c.find("input[type=hidden]"),
                $form = $c.closest("form"),
                submitButtonUsed = null,
                originSubmitLabel;

            $(document).bind('drop dragover', function (e) {
                e.preventDefault();
            });

            $form.find(":submit").on("click", function () {
                submitButtonUsed = $(this);
            });

            $form.submit(function () {
                var $submits = $(this).find(":submit");

                $submits.attr("disabled", true);

                if (0 < pendingUploads) {
                    $form.addClass("a-pending-uploads");
                    originSubmitLabel = $submits.html();
                    $submits.each(function () {
                        $(this).data("origin-label", $(this).html());
                    });
                    $submits.html(settings.pending_uploads_label);
                    submitRequested = $(this);
                    return false;
                }
                if (null !== submitButtonUsed) {
                    var submitButtonReplacement = $('<input type="hidden">');
                    submitButtonReplacement.attr("name", submitButtonUsed.attr("name"));
                    $(this).append(submitButtonReplacement);
                }
            });

            $c.on("click", ".ag-remove-file-btn", function (e) {
                var data, id, index;
                e.preventDefault();
                if (settings.multiple) {
                    data = $id.val().split(",");
                    id = $(this).attr("data-file-id");
                    index = data.indexOf(id);
                    if (id) {
                        data.splice(index, 1);
                        $id.val(data.join(","));
                    }
                } else {
                    $id.val("");
                }
                var $preview = $(this).closest(".ag-file-preview");
                if ($preview.data("jqfileupload")) {
                    $preview.data("jqfileupload").abort();
                }
                $preview.remove();

                if (!settings.multiple) {
                    $c.find(".ag-fileupload-placeholder").show();
                }
            });

            function updateProgress($progress, progress) {
                $progress.find(".progress-bar").css(
                    "width",
                    progress + "%"
                ).text(progress + "%");
                return $progress;
            }

            function closeUpload(context) {
                updateProgress(context.find(".ag-progress"), 0).hide();
                --pendingUploads;
                if (submitRequested && 0 === pendingUploads) {
                    $form.removeClass("ag-pending-uploads");
                    $form.find(":submit").each(function () {
                        $(this).html($(this).data("origin-label"));
                    });
                    submitRequested.submit();
                }
            }

            $c.find("input[type=file]").fileupload({
                dataType: "json",
                dropZone: $c,
                previewCrop: false,
                url: settings.url,
                formData: {
                    "file[_token]": settings.token,
                    "multiple": settings.multiple ? 1 : 0,
                    "filter_name": settings.filter_name,
                    "origin_filter_name": settings.origin_filter_name
                },
                paramName: "file[file][file]"
            })
                .on("fileuploadadd", function (e, data) {
                    var $files = $c.find(".ag-files");

                    if (!settings.multiple) {
                        $c.find(".ag-fileupload-placeholder").hide();
                    }

                    if (!settings.multiple) {
                        $files.html("");
                    }
                    $.each(data.files, function (index, file) {
                        var $tmpl = $c.find(".ag-template .ag-file-preview").clone();
                        $tmpl.find(".ag-file-name").html(file.name);
                        $tmpl.data("jqfileupload", data);
                        if(settings.render_order == 'prepend') {
                            data.context = $tmpl.prependTo($files);
                        } else {
                            data.context = $tmpl.appendTo($files);
                        }

                    });
                    data.submit();
                })
                .on("fileuploadprogress", function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    updateProgress(data.context.find(".ag-progress"), progress);
                })
                .on("fileuploaddone", function (e, data) {
                    var $img,
                        file,
                        val;
                    data.context.find(".ag-progress .ag-progress-bar").css("width", "100%");
                    if (!settings.multiple) {
                        $id.val("");
                    }
                    file = data.result.file;
                    val = $id.val();
                    $id.val((val ? val + "," : "") + file.id);
                    data.context.find("a.ag-file-open-btn, a.ag-file-name").attr("href", file.url);
                    data.context.find(".ag-remove-file-btn").attr("data-file-id", file.id);

                    closeUpload(data.context);

                    if (file.thumbnail_url) {
                        $img = $("<img/>", {
                            src: file.thumbnail_url
                        });
                        $img.load(function () {
                            data.context.find(".ag-file-icon").html($img.hide().fadeIn());
                        });
                    }
                })
                .on("fileuploadfail", function (e, data) {
                    var r;
                    submitRequested = false;
                    closeUpload(data.context);

                    if ("error" === data.textStatus) {
                        r = data.jqXHR.responseJSON;
                        if (r && r.errors) {
                            data.context.find(".ag-fileupload-errors").html(r.errors.join("<br>"));
                        } else {
                            data.context.find(".ag-fileupload-errors").html(settings.unknown_error_message);
                        }
                    }
                })
                .on("fileuploadsend", function () {
                    pendingUploads++;
                })
                .on("fileuploadprocessalways", function (e, data) {
                    var index = data.index,
                        i,
                        file = data.files[index],
                        node = $(data.context.children()[index]),
                        fileIconClass;
                    data.context.find(".ag-progress").show();
                    if (file.preview) {
                        node.find(".ag-file-icon").html(file.preview);
                    } else if (settings.icon_classes) {
                        if (settings.icon_classes[file.type]) {
                            fileIconClass = settings.icon_classes[file.type];
                        } else {
                            var r;
                            for (i in settings.icon_classes) {
                                r = new RegExp("^" + i.replace('/', '\\/') + "$");
                                if (r.test(file.type)) {
                                    fileIconClass = settings.icon_classes[i];
                                    break;
                                }
                            }
                        }
                        node.find(".ag-file-icon").html($("<i/>", {
                            "class": fileIconClass
                        }));
                    }
                    if (file.error) {
                        node.find(".ag-fileupload-errors").html($("<div/>").text(file.error));
                    }
                });
        }
    };

    $.fn.agileFileUpload = function (options) {
        Agile.fileUpload.setup(this, options);
        return this;
    };
}(jQuery, window.Agile));
