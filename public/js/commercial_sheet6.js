var puIdTab = [];
var qtyIdTab = [];
var remiseIdTab = [];

$('#add-commercialSheetItems').click(function () {
    //Masque le tooltip(info à bulle) du bouton
    $('[data-toggle="tooltip"]').tooltip("hide");

    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#commercialSheetItems-widgets-count').val();
    //console.log(index);
    $('#commercialSheetItems-widgets-count').val(index + 1);
    console.log('Widget-count = ' + $('#commercialSheetItems-widgets-count').val());

    $('#add-commercialSheetItems').attr('disabled', true);
    $('#add-servItems').attr('disabled', true);
    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#commercial_sheet_commercialSheetItems').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //Initialisation du facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items relatif 
    //à la qty de cet item
    tabError[index] = parseInt($('#commercialSheetItems-widgets-count').val()) - index;
    //console.log('tabError[' + index + '] = ' + tabError[index]);
    //MAJ des facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items des items à 
    //index inférieur à l'item créé
    for (let i = 0; i < index; i++) {
        tabError[i] = parseInt($('#commercialSheetItems-widgets-count').val()) - i;
        tabQtyError[i] = (tabError[i] - 1) * tabQty[i];
        //console.log('tabError[' + i + '] = ' + tabError[i]);
        //console.log('tabQtyError[' + i + '] = ' + tabQtyError[i]);
    }
    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    //$('#commercial_sheet_commercialSheetItems').append(tmpl).ready(() => {
    $('#tableRow').append(tmpl).ready(() => {
        // Select2
        var refId = '#commercial_sheet_commercialSheetItems_' + index + '_reference';
        var designationId = '#commercial_sheet_commercialSheetItems_' + index + '_designation';
        var productSKUId = '#commercial_sheet_commercialSheetItems_' + index + '_productSku';
        var productId = '#commercial_sheet_commercialSheetItems_' + index + '_product';
        var productType = '#commercial_sheet_commercialSheetItems_' + index + '_productType';
        var productPriceId = '#commercial_sheet_commercialSheetItems_' + index + '_productPrice';
        var puId = '#commercial_sheet_commercialSheetItems_' + index + '_pu';
        //var priceViewId = '#commercial_sheet_commercialSheetItems_' + index + '_priceView';//
        var qtyId = '#commercial_sheet_commercialSheetItems_' + index + '_quantity';
        var amountBrutHTId = '#commercial_sheet_commercialSheetItems_' + index + '_amountBrutHT';
        var remiseId = '#commercial_sheet_commercialSheetItems_' + index + '_remise';
        var amountNetHTId = '#commercial_sheet_commercialSheetItems_' + index + '_amountNetHT';
        var available = '#commercial_sheet_commercialSheetItems_' + index + '_available';
        var itemOfferTypeId = '#commercial_sheet_commercialSheetItems_' + index + '_itemOfferType';//

        $(productId).select2({
            width: '100%',
            //height: '300%',
            //dropdownCssClass: "custom-select"
        });

        if (isEdit) {
            var isChangedId = '#commercial_sheet_commercialSheetItems_' + index + '_isChanged';
            $(isChangedId).val(1);
        }

        //$(available).attr('type', 'number');

        //Ajout de l'option fictive de gestion des options à retire des autres select list
        var Opt = new Option("Select a Product", "-1");
        $(productId).append(Opt);
        $(productId).val("-1");

        Opt = new Option("Enter a designation", "-1");
        $(productSKUId).append(Opt);
        $(productSKUId).val("-1");

        Opt = new Option("Enter a designation", "-1");
        $(productType).append(Opt);
        $(productType).val("-1");

        Opt = new Option("Enter a unit price", "-1");
        $(productPriceId).append(Opt);
        $(productPriceId).val(0);

        $(available).val("");

        tabHideProduct.forEach(function (value, index_) {
            $(productId + " option[value='" + value + "']").remove();

        });
        tabHideSKU.forEach(function (value, index_) {
            $(productSKUId + " option[value='" + value + "']").remove();

        });
        //$("#target").val($("#target option:first").val());
        //$(".ct option[value='X']").remove();
        tabproductSKUIds[index] = $(productSKUId).val();
        tabProductIds[index] = $(productId).val();

        var qtyMax = [];
        //qtyMax['' + $(productSKUId).val()] = parseInt(availabilitiesTab[$(productSKUId).val()]);
        // $(qtyId).attr('max', qtyMax['' + $(productSKUId).val()]);
        // $(available).val(+availabilitiesTab[$(productSKUId).val()]);
        $(puId).val(0.0);
        $(qtyId).val(0);
        $(remiseId).val(0);
        $(qtyId).attr('readonly', true);
        $(remiseId).attr('readonly', true);

        $(amountBrutHTId).val(0.0);
        $(amountNetHTId).val(0.0);
        //Calcul du montant relatif à ce produit
        computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);

        var precQty = parseInt($(qtyId).val());
        qtyTotal += parseInt($(qtyId).val());
        tabQty[index] = parseInt($(qtyId).val());


        if (parseInt(qtyTotal) >= 1) $('#saveBtn').attr('disabled', false);
        else $('#saveBtn').attr('disabled', true);
        //console.log('tabQtyError[' + index + '] = ' + tabQtyError[index]);
        //Gestion des évènements de modification des entrées de l'commercialSheet item ajouté
        $(productSKUId).change(() => {
            //Retrait des options fictives précédemment ajoutées
            $(productId + " option[value='-1']").remove();
            $(productSKUId + " option[value='-1']").remove();
            $(productType + " option[value='-1']").remove();
            $(productPriceId + " option[value='-1']").remove();
            $(qtyId).attr('readonly', false);
            $(remiseId).attr('readonly', false);
            var Str = String($(productSKUId).val());
            //console.log('Product SKU value = ' + $(productSKUId).val());
            var Name = $(productSKUId + ' option[value=\"' + Str + '\"]').text();
            var puId_ = String(Name);
            $(refId).val(puId_);

            tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
            $(productId).val($(productSKUId).val());

            Str = String($(productId).val());
            //console.log('Product  value = ' + $(productId).val());
            Name = $(productId + ' option[value=\"' + Str + '\"]').text();
            puId_ = String(Name);
            $(designationId).val(puId_);

            $(productType).val($(productId).val());
            //Str = String($(productType).val());
            //console.log('Product Price value = ' + $(productType).val());
            //Name = parseInt($(productType + ' option[value=\"' + Str + '\"]').text());
            //if (Name == true) console.log('Product Type = ' + Name);
            //console.log('Offer Type = ' + $(itemOfferTypeId).val());
            //$(priceViewId).val($(puId).val());
            //$(itemOfferTypeId).val($(itemOfferTypeId).val());
            //tabproductSKUIds[index] = $(productSKUId).val();
            $('#add-commercialSheetItems').attr('disabled', false);
            $('#add-servItems').attr('disabled', false);
            tabHideProduct.forEach(function (value, index_) {
                //console.log('in foreach tabHideProduct ' + index_ + ' : value = ' + value);
                var productSKUId_ = '#commercial_sheet_commercialSheetItems_' + index_ + '_productSku';
                var productId_ = '#commercial_sheet_commercialSheetItems_' + index_ + '_product';
                // var productPriceId_ = '#commercial_sheet_commercialSheetItems_' + index_ + '_productPrice';
                $(productId_ + " option[value='" + $(productId).val() + "']").remove();

            });

            $(productPriceId).val($(productId).val());
            Str = String($(productPriceId).val());
            //console.log('Product Price value = ' + $(productPriceId).val());
            Name = $(productPriceId + ' option[value=\"' + Str + '\"]').text();
            puId_ = String(Name);
            $(puId).val(puId_);
            //console.log('Price value = ' + $(puId).val());
            //console.log('Option selected : ' + String(Name));

            //console.log('Price value = ' + $(itemOfferTypeId).val());
            qtyTotal -= parseInt($(qtyId).val());
            $('#itemsqtytotal').text(Math.abs(qtyTotal));

            if (type === 'bill') {
                Str = String($(productType).val());
                Name = parseInt($(productType + ' option[value=\"' + Str + '\"]').text());
                if (Name == true) { //Si le produit a un stock
                    $(itemOfferTypeId).val('hasStock');
                    tabItemOfferType[index] = $(itemOfferTypeId).val();
                    availabilitiesTab[tabProductIds[index]] += parseInt($(qtyId).val());
                    //console.log('OfferType Name = ' + Name);
                    qtyMax['' + tabProductIds[index]] = parseInt(availabilitiesTab[tabProductIds[index]]);
                    $(qtyId).attr('max', qtyMax['' + tabProductIds[index]]);
                    $(available).val(+availabilitiesTab[$(productId).val()]);
                }
                else {
                    //console.log('OfferTypeS Name = ' + Name);
                    $(qtyId).attr('min', '0');
                    $(qtyId).attr('max', '');
                    $(available).val("");
                    $(itemOfferTypeId).val('noStock');
                    tabItemOfferType[index] = $(itemOfferTypeId).val();
                }
            }
            else {
                $(available).val(0);
                $(itemOfferTypeId).val('noStock');
                tabItemOfferType[index] = $(itemOfferTypeId).val();
                console.log('ItemOfferTypeId = ' + $(itemOfferTypeId).val());
            }

            $(qtyId).val(0);
            $(remiseId).val(0);
            precQty = $(qtyId).val();

            var tmp = parseFloat(puId_) * parseInt($(qtyId).val());
            $(amountBrutHTId).val(tmp.toFixed(2));
            tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
            $(amountNetHTId).val(tmp.toFixed(2));

            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(puId_, qtyId, remiseId, index);

            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;

            tabHideProduct[index] = $(productId).val();
            tabHideSKU[index] = $(productSKUId).val();
            tabHideProduct[index] = $(productId).val();
            tabproductSKUIds[index] = $(productSKUId).val();
            tabProductIds[index] = $(productId).val();
            $(productId).attr('readonly', true);
            //$(productId).prop("disabled", true);
            $(productId).select2({ disabled: readonly });
            //$(productId).attr('disabled', true);
            $(productId).off('change') // désactivation de l'évènement change sur cette entrée
            $(productSKUId).attr('readonly', true);
            //$(productSKUId).attr('disabled', true);
            $(productSKUId).off('change') // désactivation de l'évènement change sur cette entrée
        });

        $(productId).change(() => {
            //Retrait des options fictives précédemment ajoutées
            $(productId + " option[value='-1']").remove();
            $(productSKUId + " option[value='-1']").remove();
            $(productType + " option[value='-1']").remove();
            $(productPriceId + " option[value='-1']").remove();
            $(qtyId).attr('readonly', false);
            $(remiseId).attr('readonly', false);

            var Str = String($(productId).val());
            //console.log('Product  value = ' + $(productId).val());
            var Name = $(productId + ' option[value=\"' + Str + '\"]').text();
            var puId_ = String(Name);
            $(designationId).val(puId_);

            tabQtyError[index] = (tabError[index] - 1) * tabQty[index];

            $(productType).val($(productId).val());
            //Str = String($(productType).val());
            //console.log('Product Price value = ' + $(productType).val());
            //Name = parseInt($(productType + ' option[value=\"' + Str + '\"]').text());
            //if (Name == true) console.log('Product Type = ' + Name);
            //console.log('Offer Type = ' + $(itemOfferTypeId).val());
            //$(priceViewId).val($(puId).val());
            //$(itemOfferTypeId).val($(itemOfferTypeId).val());
            //tabproductSKUIds[index] = $(productSKUId).val();
            $('#add-commercialSheetItems').attr('disabled', false);
            $('#add-servItems').attr('disabled', false);
            tabHideProduct.forEach(function (value, index_) {
                //console.log('in foreach tabHideProduct ' + index_ + ' : value = ' + value);
                // var productSKUId_ = '#commercial_sheet_commercialSheetItems_' + index_ + '_productSku';
                var productId_ = '#commercial_sheet_commercialSheetItems_' + index_ + '_product';
                // var productPriceId_ = '#commercial_sheet_commercialSheetItems_' + index_ + '_productPrice';
                $(productId_ + " option[value='" + $(productId).val() + "']").remove();

            });

            $(productSKUId).val($(productId).val());
            var Str = String($(productSKUId).val());
            var Name = $(productSKUId + ' option[value=\"' + Str + '\"]').text();
            var puId_ = String(Name);
            $(refId).val(puId_);

            $(productPriceId).val($(productId).val());
            Str = String($(productPriceId).val());
            //console.log('Product Price value = ' + $(productPriceId).val());
            Name = $(productPriceId + ' option[value=\"' + Str + '\"]').text();
            puId_ = String(Name);
            $(puId).val(puId_);
            //console.log('Price value = ' + $(puId).val());
            //console.log('Option selected : ' + String(Name));

            //console.log('Price value = ' + $(itemOfferTypeId).val());
            qtyTotal -= parseInt($(qtyId).val());
            $('#itemsqtytotal').text(Math.abs(qtyTotal));

            if (type === 'bill') {
                Str = String($(productType).val());
                Name = parseInt($(productType + ' option[value=\"' + Str + '\"]').text());
                if (Name == true) { //Si le produit a un stock
                    $(itemOfferTypeId).val('hasStock');
                    tabItemOfferType[index] = $(itemOfferTypeId).val();
                    availabilitiesTab[tabProductIds[index]] += parseInt($(qtyId).val());
                    //console.log('OfferType Name = ' + Name);
                    qtyMax['' + tabProductIds[index]] = parseInt(availabilitiesTab[tabProductIds[index]]);
                    $(qtyId).attr('max', qtyMax['' + tabProductIds[index]]);
                    $(available).val(+availabilitiesTab[$(productId).val()]);
                }
                else {
                    //console.log('OfferTypeS Name = ' + Name);
                    $(qtyId).attr('min', '0');
                    $(qtyId).attr('max', '');
                    $(available).val("");
                    $(itemOfferTypeId).val('noStock');
                    tabItemOfferType[index] = $(itemOfferTypeId).val();
                }
            }
            else {
                $(available).val(0);
                $(itemOfferTypeId).val('noStock');
                tabItemOfferType[index] = $(itemOfferTypeId).val();
                console.log('ItemOfferTypeId = ' + $(itemOfferTypeId).val());
            }

            $(qtyId).val(0);
            $(remiseId).val(0);
            precQty = $(qtyId).val();

            var tmp = parseFloat(puId_) * parseInt($(qtyId).val());
            $(amountBrutHTId).val(tmp.toFixed(2));
            tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
            $(amountNetHTId).val(tmp.toFixed(2));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(puId_, qtyId, remiseId, index);

            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;

            tabHideProduct[index] = $(productId).val();
            tabHideSKU[index] = $(productSKUId).val();
            tabproductSKUIds[index] = $(productSKUId).val();
            tabProductIds[index] = $(productId).val();
            $(productId).attr('readonly', true);
            //$(productId).prop("readonly", true);
            //$(productId).attr('disabled', true);
            $(productId).off('change') // désactivation de l'évènement change sur cette entrée

            $(productSKUId).attr('readonly', true);
            //$(productSKUId).attr('disabled', true);
            $(productSKUId).off('change') // désactivation de l'évènement change sur cette entrée

        });

        //Gestion de l'évènement de changement de valeur de la remise sur l'article 
        //pour mettre à jour les montants
        $(remiseId).change(() => {
            var tmp = parseFloat($(puId).val()) * parseInt($(qtyId).val());
            if (!$(remiseId).val() == false && $.isNumeric($(remiseId).val())) {
                if (!isNaN(parseInt($(remiseId).val()))) {
                    if ($(remiseId).val() >= 0) {
                        //console.log('P.U = ' + $(puId).val());
                        $(amountBrutHTId).val(tmp.toFixed(2));
                        tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
                        $(amountNetHTId).val(tmp.toFixed(2));
                        //Calcul du montant relatif à ce produit
                        computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);
                    }
                }
            }
            else {
                $(remiseId).val(0);
                $(amountBrutHTId).val(tmp);
                tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
                $(amountNetHTId).val(tmp);
                //Calcul du montant relatif à ce produit
                computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);
            }

            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;
        });

        //Gestion de l'évènement de changement de valeur de la quantité du produit 
        //pour mettre à jour les montants
        $(qtyId).change(() => {

            var add = false;
            var diff = 0;
            //console.log('old precQty = ' + parseInt(precQty));

            if (!$(qtyId).val() == false && $.isNumeric($(qtyId).val())) {
                if (!isNaN(parseInt($(qtyId).val()))) {
                    if ($(qtyId).val() >= 0) {
                        //console.log('Qty = ' + parseInt($(qtyId).val()));
                        if (parseInt(precQty) < parseInt($(qtyId).val())) add = false;
                        else add = true;
                        diff = Math.abs(parseInt(precQty) - parseInt($(qtyId).val()));
                        //console.log('diff = ' + diff);
                        if ($(itemOfferTypeId).val() === 'hasStock') {
                            // if (availabilities[$(productSKUId).val()] >= $(qtyId).val()) {
                            if (availabilities[$(productId).val()] >= $(qtyId).val()) {
                                if (!add) {
                                    //console.log('add = ' + add);
                                    // availabilitiesTab[$(productSKUId).val()] -= diff;
                                    availabilitiesTab[$(productId).val()] -= diff;
                                    qtyTotal += diff;
                                    // availabilitiesTab[$(productSKUId).val()] = Math.abs(availabilitiesTab[$(productSKUId).val()]);
                                    availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                                    //qtyMax['' + $(productSKUId).val()] -= diff;
                                }
                                else {
                                    //console.log('add = ' + add);
                                    // availabilitiesTab[$(productSKUId).val()] += (diff);
                                    availabilitiesTab[$(productId).val()] += (diff);
                                    qtyTotal -= diff;
                                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                                    //qtyMax['' + $(productSKUId).val()] += diff;
                                }
                            }
                            else {
                                // $(qtyId).val(availabilities[$(productSKUId).val()]);
                                // availabilitiesTab[$(productSKUId).val()] = 0;
                                // qtyTotal += parseInt(availabilities[$(productSKUId).val()]);
                                $(qtyId).val(availabilities[$(productId).val()]);
                                availabilitiesTab[$(productId).val()] = 0;
                                qtyTotal += parseInt(availabilities[$(productId).val()]);
                                $('#itemsqtytotal').text(Math.abs(qtyTotal));

                            }
                        }
                        else {

                            if (!add) {
                                //console.log('add = ' + add);
                                // availabilitiesTab[$(productSKUId).val()] -= diff;
                                //availabilitiesTab[$(productId).val()] -= diff;
                                qtyTotal += diff;
                                // availabilitiesTab[$(productSKUId).val()] = Math.abs(availabilitiesTab[$(productSKUId).val()]);
                                //availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                                $('#itemsqtytotal').text(Math.abs(qtyTotal));
                                //qtyMax['' + $(productSKUId).val()] -= diff;
                            }
                            else {
                                console.log('add = ' + add);
                                // availabilitiesTab[$(productSKUId).val()] += (diff);
                                //availabilitiesTab[$(productId).val()] += (diff);
                                qtyTotal -= diff;
                                $('#itemsqtytotal').text(Math.abs(qtyTotal));
                                //qtyMax['' + $(productSKUId).val()] += diff;
                            }


                        }

                        //$(qtyId).attr('max', qtyMax['' + $(productSKUId).val()]);
                        // $(available).val(+availabilitiesTab[$(productSKUId).val()]);

                        //console.log(availabilities[$(productSKUId).val()]);

                    }
                    else {
                        $(qtyId).val(0);
                        if ($(itemOfferTypeId).val() === 'hasStock') availabilitiesTab[$(productId).val()] -= parseInt(precQty);
                        // availabilitiesTab[$(productSKUId).val()] = Math.abs(availabilitiesTab[$(productSKUId).val()]);
                        availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                        qtyTotal -= parseInt(precQty);
                        $('#itemsqtytotal').text(Math.abs(qtyTotal));
                    }
                }
                else {

                    $(qtyId).val(0);
                    if ($(itemOfferTypeId).val() === 'hasStock') {
                        availabilitiesTab[$(productId).val()] -= parseFloat(precQty);
                        // availabilitiesTab[$(productSKUId).val()] = Math.abs(availabilitiesTab[$(productSKUId).val()]);
                        availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                    }
                    qtyTotal -= parseFloat(precQty);
                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                }
            }
            else {
                $(qtyId).val(0);
                if ($(itemOfferTypeId).val() === 'hasStock') {
                    availabilitiesTab[$(productId).val()] = availabilitiesTab[$(productId).val()] + parseInt(precQty);
                    // availabilitiesTab[$(productSKUId).val()] = Math.abs(availabilitiesTab[$(SKUId).val()]);
                    availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                }
                qtyTotal = qtyTotal - parseInt(precQty);
                $('#itemsqtytotal').text(Math.abs(qtyTotal));
            }

            if ($(itemOfferTypeId).val() === 'hasStock') {
                $(available).val(+availabilitiesTab[$(productId).val()]);

            }
            tabQty[index] = parseInt($(qtyId).val());
            tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
            $(productPriceId).val($(productId).val());

            Str = String($(productPriceId).val());
            //console.log('Product Price value = ' + $(productPriceId).val());
            Name = $(productPriceId + ' option[value=\"' + Str + '\"]').text();
            puId_ = String(Name);
            $(puId).val(puId_);

            if (parseInt(qtyTotal) >= 1) $('#saveBtn').attr('disabled', false);
            else $('#saveBtn').attr('disabled', true);

            //console.log('Option selected : ' + String(Name));
            var tmp = parseFloat(puId_) * parseInt($(qtyId).val());
            $(amountBrutHTId).val(tmp.toFixed(2));
            tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
            $(amountNetHTId).val(tmp.toFixed(2));

            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(puId_, qtyId, remiseId, index);
            precQty = $(qtyId).val();
            //console.log('new precQty = ' + parseInt(precQty));
            //console.log('precQty = ' + precQty);
        });

        // const commercialSheetItemsCounter_ = +$('#commercialSheet_commercialSheetItems div.nbItems').length;
        // console.log('commercialSheetItemsCounter_ = ' + commercialSheetItemsCounter_);
        // for (let index_ = 0; index_ < commercialSheetItemsCounter_; index_++) {
        //     var SKUId_ = '#commercialSheet_commercialSheetItems_' + index_ + '_sku';
        //     var productId_ = '#commercialSheet_commercialSheetItems_' + index_ + '_product';
        //     $(SKUId_ + " option").each(function () {
        //         //console.log($(this).html());
        //         $(this).removeClass('d-none');
        //     });
        //     var Str_ = String($(SKUId).val());
        //     tabHideSKU.push(Str_);
        //     //var Name_ = $(SKUId_ + ' option[value=\"' + Str_ + '\"]').text();
        //     tabHideSKU.forEach(element => {
        //         $(SKUId_ + " option[value='" + element + "']").attr('selected', '');
        //         $(SKUId_ + " option[value='" + element + "']").addClass('d-none');
        //         $(productId_ + " option[value='" + element + "']").attr('selected', '');
        //         $(productId_ + " option[value='" + element + "']").addClass('d-none');

        //     });


        // }

        handleDeleteButton();
    });
    //console.log($('#commercialSheet_commercialSheetItems').html());
    //console.log('widgets-count = ' + $('#commercialSheetItems-widgets-count').val());
    $('#commercial_sheet_commercialSheetItems_' + index + '_quantity').attr('type', 'number');
    $('#commercial_sheet_commercialSheetItems_' + index + '_pu').attr('type', 'number');
    $('#commercial_sheet_commercialSheetItems_' + index + '_remise').attr('type', 'number');

    //$('.colpriceView_commercial_sheet_commercialSheetItems_' + index).removeClass('d-none');
    //$('.col3_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
    // $('#commercial_sheet_commercialSheetItems_' + index + '_reference').attr('readonly', true);
    $('.coldesignation_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
    $('.colproduct_commercial_sheet_commercialSheetItems_' + index).removeClass('d-none');
    $('#commercial_sheet_commercialSheetItems_' + index + '_pu').attr('readonly', true);
    $('#commercial_sheet_commercialSheetItems_' + index + '_pu').attr('type', 'text');


    if (type !== 'bill') {
        $('.colavai_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
        $('#commercial_sheet_commercialSheetItems_' + index + '_available').addClass('d-none');
        $('#commercial_sheet_commercialSheetItems_' + index + '_available').attr('required', false);
        //console.log('available required false');
    }
    if (isEdit == false) {
        // $('.colRef_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
        // $('.colSKU_commercial_sheet_commercialSheetItems_' + index).removeClass('d-none');
    }
    // tabSKUIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_sku';
    // tabProductIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_product';
    // tabPriceIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_price';
    // tabProductQtyIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_quantity';

});

$('#add-servItems').click(function () {
    //Masque le tooltip(info à bulle) du bouton
    $('[data-toggle="tooltip"]').tooltip("hide");

    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#commercialSheetItems-widgets-count').val();
    //console.log(index);
    $('#commercialSheetItems-widgets-count').val(index + 1);

    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#commercial_sheet_commercialSheetItems').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //Initialisation du facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items relatif 
    //à la qty de cet item
    tabError[index] = parseInt($('#commercialSheetItems-widgets-count').val()) - index;
    //console.log('tabError[' + index + '] = ' + tabError[index]);
    //MAJ des facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items des items à 
    //index inférieur à l'item créé
    for (let i = 0; i < index; i++) {
        tabError[i] = parseInt($('#commercialSheetItems-widgets-count').val()) - i;
        tabQtyError[i] = (tabError[i] - 1) * tabQty[i];
        console.log('tabError[' + i + '] = ' + tabError[i]);
        console.log('tabQtyError[' + i + '] = ' + tabQtyError[i]);
    }
    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    //$('#commercial_sheet_commercialSheetItems').append(tmpl).ready(() => {
    $('#tableRow').append(tmpl).ready(() => {
        // var SKUId = '#commercial_sheet_commercialSheetItems_' + index + '_sku';
        //var productId = '#commercial_sheet_commercialSheetItems_' + index + '_product';
        var offerInId = '#commercial_sheet_commercialSheetItems_' + index + '_offerIn';
        var puId = '#commercial_sheet_commercialSheetItems_' + index + '_pu';
        var priceInId = '#commercial_sheet_commercialSheetItems_' + index + '_priceIn';
        var qtyId = '#commercial_sheet_commercialSheetItems_' + index + '_quantity';
        var amountBrutHTId = '#commercial_sheet_commercialSheetItems_' + index + '_amountBrutHT';
        var remiseId = '#commercial_sheet_commercialSheetItems_' + index + '_remise';
        var amountNetHTId = '#commercial_sheet_commercialSheetItems_' + index + '_amountNetHT';
        //var available = '#commercial_sheet_commercialSheetItems_' + index + '_available';
        var itemOfferTypeId = '#commercial_sheet_commercialSheetItems_' + index + '_itemOfferType';

        $(itemOfferTypeId).val('Simple');

        tabItemOfferType[index] = $(itemOfferTypeId).val();
        //tabSKUIds[index] = $(SKUId).val();
        //tabProductIds[index] = $(productId).val();
        if (isNaN(parseFloat($(puId).val())) || parseFloat($(puId).val()) < 0 || !$(puId).val() == true) {
            $(puId).val(0.0);
        }
        if (isNaN(parseFloat($(qtyId).val())) || parseFloat($(qtyId).val()) < 0 || !$(qtyId).val() == true) {
            $(qtyId).val(0);
        }

        $(remiseId).val(0.0);
        $(remiseId).attr('readonly', true);
        $(qtyId).attr('readonly', true);

        var precQty = parseInt($(qtyId).val());
        qtyTotal += parseInt($(qtyId).val());
        tabQty[index] = parseInt($(qtyId).val());
        tabQtyError[index] = (tabError[index] - 1) * tabQty[index];

        $(amountBrutHTId).val(0.0);
        $(amountNetHTId).val(0.0);
        //Calcul du montant relatif à ce produit
        computeItemsAmountTab(parseFloat($(puId).val()), qtyId, remiseId, index);

        //Gestion des évènements de modification du P.U de l'article ajouté
        $(puId).change(() => {
            //$(puId).val($(productId).val());

            //tabSKUIds[index] = $(SKUId).val();
            //tabProductIds[index] = $(productId).val();

            if (!$(this).val() == false && $.isNumeric($(this).val())) {
                if (!isNaN(parseInt($(this).val()))) {
                    if ($(this).val() >= 0) {
                        $(this).val(0.0);
                        $(remiseId).attr('readonly', true);
                        $(qtyId).attr('readonly', true);
                    }
                }
            }
            else {
                $(remiseId).attr('readonly', false);
                $(qtyId).attr('readonly', false);
            }
            var tmp = parseFloat($(puId).val()) * parseInt($(qtyId).val());
            $(amountBrutHTId).val(tmp.toFixed(2));
            tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
            $(amountNetHTId).val(tmp.toFixed(2));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);
            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;
        });

        //Gestion de l'évènement de changement de valeur de la remise sur l'article 
        //pour mettre à jour les montants
        $(remiseId).change(() => {
            if (!$(remiseId).val() == false && $.isNumeric($(remiseId).val())) {
                if (!isNaN(parseInt($(remiseId).val()))) {
                    if ($(remiseId).val() >= 0) {
                        //console.log('P.U = ' + $(puId).val());
                        var tmp = parseFloat($(puId).val()) * parseInt($(qtyId).val());
                        $(amountBrutHTId).val(tmp.toFixed(2));
                        tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
                        $(amountNetHTId).val(tmp.toFixed(2));
                        //Calcul du montant relatif à ce produit
                        computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);
                    }
                }
            }
            else {
                $(remiseId).val(0);
                var tmp = parseFloat($(puId).val()) * parseInt($(qtyId).val());
                $(amountBrutHTId).val(tmp);
                tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
                $(amountNetHTId).val(tmp);
                //Calcul du montant relatif à ce produit
                computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);
            }

            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;

        });

        //Gestion de l'évènement de changement de valeur de la quantité du produit 
        //pour mettre à jour les montants
        $(qtyId).change(() => {
            var add = false;
            var diff = 0;
            if (!$(qtyId).val() == false && $.isNumeric($(qtyId).val())) {
                if (!isNaN(parseInt($(qtyId).val()))) {
                    if ($(qtyId).val() >= 0) {
                        console.log('precQty = ' + precQty);
                        console.log('Qty = ' + $(qtyId).val());
                        if (parseInt(precQty) < parseInt($(qtyId).val())) add = false;
                        else add = true;
                        diff = Math.abs(parseInt(precQty) - parseInt($(qtyId).val()));
                        console.log('diff = ' + diff);
                        // if (availabilities[$(SKUId).val()] >= $(qtyId).val()) {
                        if (!add) {
                            console.log('add = ' + add);
                            // availabilitiesTab[$(SKUId).val()] -= diff;
                            //availabilitiesTab[$(productId).val()] -= diff;
                            qtyTotal += diff;
                            // availabilitiesTab[$(SKUId).val()] = Math.abs(availabilitiesTab[$(SKUId).val()]);
                            //availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                            $('#itemsqtytotal').text(Math.abs(qtyTotal));
                            //qtyMax['' + $(SKUId).val()] -= diff;
                        }
                        else {
                            console.log('add = ' + add);
                            // availabilitiesTab[$(SKUId).val()] += (diff);
                            //availabilitiesTab[$(productId).val()] += (diff);
                            qtyTotal -= diff;
                            $('#itemsqtytotal').text(Math.abs(qtyTotal));
                            //qtyMax['' + $(SKUId).val()] += diff;
                        }

                        //$(qtyId).attr('max', qtyMax['' + $(SKUId).val()]);
                        // $(available).val(+availabilitiesTab[$(SKUId).val()]);
                        //$(available).val(+availabilitiesTab[$(productId).val()]);
                        //console.log(availabilities[$(SKUId).val()]);
                    }
                    else {
                        $(qtyId).val(0);
                        qtyTotal -= parseInt(precQty);
                        $('#itemsqtytotal').text(Math.abs(qtyTotal));
                    }
                }
                else {
                    $(qtyId).val(0);
                    qtyTotal -= parseInt(precQty);
                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                }
            }
            else {
                $(qtyId).val(0);
                qtyTotal = qtyTotal - parseFloat(precQty);
                $('#itemsqtytotal').text(Math.abs(qtyTotal));
            }

            if (parseInt(qtyTotal) >= 1) $('#saveBtn').attr('disabled', false);
            else $('#saveBtn').attr('disabled', true);

            tabQty[index] = parseInt($(qtyId).val());
            tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
            var tmp = parseFloat($(puId).val()) * parseInt($(qtyId).val());
            $(amountBrutHTId).val(tmp.toFixed(2));
            tmp = tmp - ((tmp * parseFloat($(remiseId).val())) / 100.0);
            $(amountNetHTId).val(tmp.toFixed(2));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab($(puId).val(), qtyId, remiseId, index);
            precQty = parseInt($(qtyId).val());
            //console.log('precQty = ' + precQty);
            puIdTab[index] = $(puId).val();
            qtyIdTab[index] = qtyId;
            remiseIdTab[index] = remiseId;

        });

        handleDeleteButton();
    });
    //console.log($('#commercialSheet_commercialSheetItems').html());
    //handleDeleteButton();
    $('#commercial_sheet_commercialSheetItems_' + index + '_quantity').attr('type', 'number');
    $('#commercial_sheet_commercialSheetItems_' + index + '_pu').attr('type', 'number');
    $('#commercial_sheet_commercialSheetItems_' + index + '_remise').attr('type', 'number');

    /*$('#commercial_sheet_commercialSheetItems_' + index + '_product').attr('required', false);
    $('#commercial_sheet_commercialSheetItems_' + index + '_price').attr('required', false);
    $('#commercial_sheet_commercialSheetItems_' + index + '_offerIn').attr('required', true);
    $('#commercial_sheet_commercialSheetItems_' + index + '_priceIn').attr('required', true);*/

    $('#commercial_sheet_commercialSheetItems_' + index + '_available').attr('readonly', 'true');
    $('#commercial_sheet_commercialSheetItems_' + index + '_available').val('');

    if (type !== 'bill') {
        $('.colavai_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
        $('#commercial_sheet_commercialSheetItems_' + index + '_available').addClass('d-none');
        $('#commercial_sheet_commercialSheetItems_' + index + '_available').attr('required', false);
        // console.log('available required false');
    }

    // $('.colavai_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
    // $('.col1_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
    // $('.col3_commercial_sheet_commercialSheetItems_' + index).addClass('d-none');
    // $('.col2_commercial_sheet_commercialSheetItems_' + index).removeClass('d-none');
    // $('.col4_commercial_sheet_commercialSheetItems_' + index).removeClass('d-none');
    // tabSKUIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_sku';
    // tabProductIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_product';
    // tabPriceIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_price';
    // tabProductQtyIds[index] = '#commercialSheet_commercialSheetItems_' + index + '_quantity';

});

if (parseInt(qtyTotal) >= 1) $('#saveBtn').attr('disabled', false);
else $('#saveBtn').attr('disabled', true);

handleDeleteButton();
updateCounter();

//Calcul du montant total de la promo
computeTotalPromoAmount();

function updateCounter() {
    const commercialSheetItemsCounter = +$('#commercial_sheet_commercialSheetItems div.nbItems').length;
    //const reductionsCounter = +$('#commercial_sheet_reductions div.form-group').length;

    $('#commercialSheetItems-widgets-count').val(commercialSheetItemsCounter);
    //$('#reductions-widgets-count').val(reductionsCounter);
    //console.log('widgets-count = ' + $('#commercialSheetItems-widgets-count').val());
}

function handleDeleteButton() {
    //Je gère l'action du click sur les boutton possédant l'attribut data-action = "delete"
    $('button[data-action="delete"]').click(function () {
        //Je récupère l'identifiant de la cible(target) à supprimer en recherchant 
        //dans les attributs data-[quelque chose](grâce à dataset) celui dont quelque chose = target (grâce à target)
        const target = this.dataset.target;
        var str = String(target);
        var subItems = String('_commercialSheetItems_');
        var posItems = str.indexOf(subItems);
        //var subReductions = String('_reductions_');
        // var posReductions = str.indexOf(subReductions);
        var index = 0;
        if (posItems > 0) {
            //console.log(str.substr(posItems + subItems.length));
            index = parseInt(str.substr(posItems + subItems.length));
            console.log('subItems index = ' + index);
            // itemsAmount.splice(index, 1);

            amountBrutHT[index] = 0; // Mise à zéro du montant Brut HT de l'article
            itemReductionTab[index] = 0; // Mise à zéro du montant de réduction de l'article
            itemsAmount[index] = 0; // Mise à zéro du montant Net HT de l'article

            if (tabItemOfferType[index] === 'hasStock') {
                //tabHideSKU.splice(index, 1);
                // tabHideProduct.splice(index, 1);
                //console.log(itemsAmount);
                //availabilitiesTab[tabSKUIds[index]] = availabilities[tabSKUIds[index]];
                availabilitiesTab[tabProductIds[index]] = availabilities[tabProductIds[index]];

            }
            $('#add-commercialSheetItems').attr('disabled', false);
            $('#add-servItems').attr('disabled', false);
            tabHideProduct[index] = '';
            tabHideSKU[index] = '';

            //console.log('index = ' + index);
            //console.log('before Qty total = ' + qtyTotal);
            //console.log('tabQtyError[' + index + '] = ' + tabQtyError[index]);
            if (!isNaN(tabQtyError[index])) {
                console.log('tabQtyError = ' + tabQtyError[index]);
                console.log('tabQty = ' + tabQty[index]);
                qtyTotal = qtyTotal - parseInt(tabQty[index]) + tabQtyError[index];
                $('#itemsqtytotal').text(Math.abs(qtyTotal));
                console.log('after Qty total = ' + qtyTotal);
                tabQtyError[index] = 0;

            }

            puIdTab[index] = 0;

            //computeItemsAmountTab(puIdTab[index], qtyIdTab[index], remiseIdTab[index], index);
            //Calcul du sous montant Total des items
            computeItemsAmountSubTotal();
        }
        /*else if (posReductions > 0) {
            //console.log(str.substr(posReductions + subReductions.length));
            index = parseInt(str.substr(posReductions + subReductions.length));
            //console.log('subReductions index = ' + index);
            // reductionsAmount.splice(index, 1);
            reductionsAmount[index] = 0;
            //console.log(reductionsAmount);

            //Calcul de la réduction Total sur les pots retournés
            computeReductionsAmountSubtotal();
        }*/
        //console.log(target);
        $(target).remove();
        //updateCounter();
    });
};

$('#commercial_sheet_paymentStatus').change(function () {
    if ($(this).is(':checked')) {
        //console.log('Payment Status = ' + $(this).is(':checked'));
        $('#commercial_sheet_advancePayment').attr('readonly', true);
    }
    else if (!$('#commercial_sheet_completedStatus').is(':checked')) {
        $('#commercial_sheet_advancePayment').attr('readonly', false);
        $('#commercial_sheet_advancePayment').val(0);
    }
    computeTotalAmount();
});

$('#commercial_sheet_completedStatus').change(function () {
    if ($(this).is(':checked')) {
        //console.log('Payment Status = ' + $(this).is(':checked'));
        $('#commercial_sheet_advancePayment').attr('readonly', true);
    }
    else if (!$('#commercial_sheet_paymentStatus').is(':checked')) {
        $('#commercial_sheet_advancePayment').attr('readonly', false);
        $('#commercial_sheet_advancePayment').val(0);
    }
    computeTotalAmount();
});

$('#commercial_sheet_advancePayment').change(() => {
    if (!$('#commercial_sheet_advancePayment').val() == false && $.isNumeric($('#commercial_sheet_advancePayment').val())) {

    }
    else $('#commercial_sheet_advancePayment').val(0);
    computeTotalAmount();
});