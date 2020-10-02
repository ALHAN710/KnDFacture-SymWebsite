var tabSKUIds = [];
var tabHideSKU = ['', ''];
var tabHideProduct = ['', ''];
var tabProductIds = [];
var tabPriceIds = [];
var tabProductQtyIds = [];

var tabQty = [];
var tabOfferTypeIn = [];
var qtyTotal = 0;
var tabError = [];
var tabQtyError = [];
var itemsAmount = [];
var itemsAmountSubTotal = 0;

// var reductionsAmount = [];
// var reductionsAmountSubtotal = 0;

var deliveryReduction = 0;

var totalPromoAmount = 0;

var totalAmount = 0;

//console.log(availabilities);
//var availabilitiesTab = availabilities;
var itemsReduction = 0.0;
var fixReductions = 0;
var deliveryFees = 0;

if (isNaN(parseFloat($('#commercial_sheet_itemsReduction').val())) || parseFloat($('#commercial_sheet_itemsReduction').val()) < 0 || !$('#commercial_sheet_itemsReduction').val() == true) {
    $('#commercial_sheet_itemsReduction').val(0.0);
}
itemsReduction = parseFloat($('#commercial_sheet_itemsReduction').val());
//Gestion des évènements de modification sur l'entrée numérique du pourcentage 
//de réduction sur la commande
$('#commercial_sheet_itemsReduction').change(function () {
    console.log($(this).val());
    if (isNaN(parseFloat($(this).val())) || parseFloat($(this).val()) < 0 || !$(this).val() == true) {
        $(this).val(0.0);
        itemsReduction = 0.0;
    }
    else {
        itemsReduction = (parseFloat($(this).val()) / 100) * parseFloat(itemsAmountSubTotal);
    }

    //Calcul du montant total de la promo
    computeTotalPromoAmount();

});
//console.log(isNaN(parseFloat($('#commercial_sheet_fixReduction').val())) ? 0 : $('#commercial_sheet_fixReduction').val());

if (isNaN(parseFloat($('#commercial_sheet_fixReduction').val())) || parseFloat($('#commercial_sheet_fixReduction').val()) < 0 || !$('#commercial_sheet_fixReduction').val() == true) {
    $('#commercial_sheet_fixReduction').val(0.0);
}
fixReductions = parseFloat($('#commercial_sheet_fixReduction').val());
//Gestion des évènements de modification sur l'entrée numérique du montant 
//de réduction fixe
$('#commercial_sheet_fixReduction').change(function () {
    if (isNaN(parseFloat($(this).val())) || parseFloat($(this).val()) < 0 || !$(this).val() == true) {
        $(this).val(0.0);
    }

    fixReductions = parseFloat($(this).val());

    //Calcul du montant total de la promo
    computeTotalPromoAmount();

});

if (isNaN(parseFloat($('#commercial_sheet_deliveryFees').val())) || parseFloat($('#commercial_sheet_deliveryFees').val()) < 0 || !$('#commercial_sheet_deliveryFees').val() == true) {
    $('#commercial_sheet_deliveryFees').val(0);
}
deliveryFees = parseFloat($('#commercial_sheet_deliveryFees').val());

//Gestion des évènements de modification sur l'entrée numérique du prix de livraison 
$('#commercial_sheet_deliveryFees').change(function () {
    if (isNaN(parseFloat($(this).val())) || parseFloat($(this).val()) < 0 || !$(this).val() == true) {
        $(this).val(0.0);
    }
    deliveryFees = parseFloat($(this).val());

    //Calcul du montant total de la promo
    computeTotalPromoAmount();

});

$('#add-orderItems').click(function () {
    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#orderItems-widgets-count').val();
    console.log(index);
    $('#orderItems-widgets-count').val(index + 1);

    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#commercial_sheet_orderItems').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //Initialisation du facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items relatif 
    //à la qty de cet item
    tabError[index] = parseInt($('#orderItems-widgets-count').val()) - index;
    console.log('tabError[' + index + '] = ' + tabError[index]);
    //MAJ des facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items des items à 
    //index inférieur à l'item créé
    for (let i = 0; i < index; i++) {
        tabError[i] = parseInt($('#orderItems-widgets-count').val()) - i;
        tabQtyError[i] = (tabError[i] - 1) * tabQty[i];
        console.log('tabError[' + i + '] = ' + tabError[i]);
        console.log('tabQtyError[' + i + '] = ' + tabQtyError[i]);
    }
    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    $('#commercial_sheet_orderItems').append(tmpl).ready(() => {
        // var SKUId = '#commercial_sheet_orderItems_' + index + '_sku';
        var productId = '#commercial_sheet_orderItems_' + index + '_product';
        var priceId = '#commercial_sheet_orderItems_' + index + '_price';
        var priceViewId = '#commercial_sheet_orderItems_' + index + '_priceView';
        var qtyId = '#commercial_sheet_orderItems_' + index + '_quantity';
        var amountId = '#commercial_sheet_orderItems_' + index + '_amount';
        var available = '#commercial_sheet_orderItems_' + index + '_available';
        var offerTypeId = '#commercial_sheet_orderItems_' + index + '_offerType';
        var offerTypeInId = '#commercial_sheet_orderItems_' + index + '_offerTypeIn';

        $(offerTypeInId).val('Product');

        tabOfferTypeIn[index] = $(offerTypeInId).val();

        //$(available).attr('type', 'number');

        //tabSKUIds[index] = $(SKUId).val();
        tabProductIds[index] = $(productId).val();

        var qtyMax = [];
        //qtyMax['' + $(SKUId).val()] = parseInt(availabilitiesTab[$(SKUId).val()]);
        // $(qtyId).attr('max', qtyMax['' + $(SKUId).val()]);
        // $(available).val(+availabilitiesTab[$(SKUId).val()]);

        if (isNaN(parseFloat($(priceId).val())) || parseFloat($(priceId).val()) < 0 || !$(priceId).val() == true) {
            $(priceId).val(0.0);
        }

        if (isNaN(parseFloat($(qtyId).val())) || parseFloat($(qtyId).val()) < 0 || !$(qtyId).val() == true) {
            $(qtyId).val(0);
        }
        var Str = String($(priceId).val());
        //console.log('Price value = ' + $(priceId).val());
        var Name = $(priceId + ' option[value=\"' + Str + '\"]').text();
        var priceId_ = String(Name);
        $(priceViewId).val(priceId_);
        //console.log('Option selected : ' + String(Name));
        console.log('Offer Type = ' + $(offerTypeId).val());

        Str = String($(offerTypeId).val());
        //console.log('Price value = ' + $(offerTypeId).val());
        Name = $(offerTypeId + ' option[value=\"' + Str + '\"]').text();
        if (Name === 'Product') {
            qtyMax['' + $(productId).val()] = parseInt(availabilitiesTab[$(productId).val()]);
            $(qtyId).attr('max', qtyMax['' + $(productId).val()]);
            $(available).val(+availabilitiesTab[$(productId).val()]);
        }
        else {
            $(qtyId).attr('min', '0');
            $(qtyId).attr('max', '');
            $(available).val("");

        }

        $(amountId).val(parseFloat(priceId_) * parseInt($(qtyId).val()));
        //Calcul du montant relatif à ce produit
        computeItemsAmountTab(priceId_, qtyId, index);

        var precQty = parseInt($(qtyId).val());
        qtyTotal += parseInt($(qtyId).val());
        tabQty[index] = parseInt($(qtyId).val());
        tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
        //console.log('tabQtyError[' + index + '] = ' + tabQtyError[index]);
        //Gestion des évènements de modification des entrées de l'order item ajouté
        /*$(SKUId).change(() => {
            // console.log('SKU value = ' + $(SKUId).val());
            // var Str = String($(SKUId).val());
            // var Name = $(SKUId + ' option[value=\"' + Str + '\"]').text();
            // console.log('Option selected : ' + String(Name));
            qtyMax['' + $(SKUId).val()] = parseInt(availabilitiesTab[$(SKUId).val()]);
            $(qtyId).attr('max', qtyMax['' + $(SKUId).val()]);
            $(available).val(+availabilitiesTab[$(SKUId).val()]);

            //Modification des champs produits et prix correspondants au sku selectionné
            $(productId).val($(SKUId).val());
            $(priceId).val($(SKUId).val());

            tabSKUIds[index] = $(SKUId).val();

            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(priceId, qtyId, index);
        });*/

        $(productId).change(() => {
            $(priceId).val($(productId).val());
            $(offerTypeId).val($(productId).val());
            console.log('Offer Type = ' + $(offerTypeId).val());
            $(priceViewId).val($(priceId).val());
            //tabSKUIds[index] = $(SKUId).val();
            tabProductIds[index] = $(productId).val();

            var Str = String($(priceId).val());
            //console.log('Price value = ' + $(priceId).val());
            var Name = $(priceId + ' option[value=\"' + Str + '\"]').text();
            var priceId_ = String(Name);
            $(priceViewId).val(priceId_);
            //console.log('Option selected : ' + String(Name));

            Str = String($(offerTypeId).val());
            //console.log('Price value = ' + $(offerTypeId).val());
            Name = $(offerTypeId + ' option[value=\"' + Str + '\"]').text();
            if (Name === 'Product') {
                console.log('OfferType NameS = ' + Name);
                qtyMax['' + $(productId).val()] = parseInt(availabilitiesTab[$(productId).val()]);
                $(qtyId).attr('max', qtyMax['' + $(productId).val()]);
                $(available).val(+availabilitiesTab[$(productId).val()]);
            }
            else {
                console.log('OfferTypeS Name = ' + Name);
                $(qtyId).attr('min', '0');
                $(qtyId).attr('max', '');
                $(available).val("");

            }

            $(amountId).val(parseFloat(priceId_) * parseInt($(qtyId).val()));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(priceId_, qtyId, index);

        });

        //Gestion de l'évènement de changement de vleur de la quantité du produit 
        //pour mettre à jour les montants
        $(qtyId).change(() => {
            // var Str = String($(SKUId).val());
            // var Name = $(SKUId + ' option[value=\"' + Str + '\"]').text();
            var Str = String($(productId).val());
            var Name = $(productId + ' option[value=\"' + Str + '\"]').text();
            //console.log('SKU (' + String(Name) + ') Qty = ' + $(qtyId).val());
            Str = String($(offerTypeId).val());
            //console.log('Price value = ' + $(offerTypeId).val());
            Name = $(offerTypeId + ' option[value=\"' + Str + '\"]').text();

            var add = false;
            var diff = 0;
            console.log('old precQty = ' + parseInt(precQty));

            if (!$(qtyId).val() == false) {
                if (!isNaN(parseInt($(qtyId).val()))) {
                    if ($(qtyId).val() >= 0) {
                        console.log('Qty = ' + parseInt($(qtyId).val()));
                        if (parseInt(precQty) < parseInt($(qtyId).val())) add = false;
                        else add = true;
                        diff = Math.abs(parseInt(precQty) - parseInt($(qtyId).val()));
                        console.log('diff = ' + diff);
                        if (Name === 'Product') {
                            // if (availabilities[$(SKUId).val()] >= $(qtyId).val()) {
                            if (availabilities[$(productId).val()] >= $(qtyId).val()) {
                                if (!add) {
                                    console.log('add = ' + add);
                                    // availabilitiesTab[$(SKUId).val()] -= diff;
                                    availabilitiesTab[$(productId).val()] -= diff;
                                    qtyTotal += diff;
                                    // availabilitiesTab[$(SKUId).val()] = Math.abs(availabilitiesTab[$(SKUId).val()]);
                                    availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                                    //qtyMax['' + $(SKUId).val()] -= diff;
                                }
                                else {
                                    console.log('add = ' + add);
                                    // availabilitiesTab[$(SKUId).val()] += (diff);
                                    availabilitiesTab[$(productId).val()] += (diff);
                                    qtyTotal -= diff;
                                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                                    //qtyMax['' + $(SKUId).val()] += diff;
                                }
                            }
                            else {
                                // $(qtyId).val(availabilities[$(SKUId).val()]);
                                // availabilitiesTab[$(SKUId).val()] = 0;
                                // qtyTotal += parseInt(availabilities[$(SKUId).val()]);
                                $(qtyId).val(availabilities[$(productId).val()]);
                                availabilitiesTab[$(productId).val()] = 0;
                                qtyTotal += parseInt(availabilities[$(productId).val()]);
                                $('#itemsqtytotal').text(Math.abs(qtyTotal));

                            }
                        }
                        else {

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


                        }

                        //$(qtyId).attr('max', qtyMax['' + $(SKUId).val()]);
                        // $(available).val(+availabilitiesTab[$(SKUId).val()]);

                        //console.log(availabilities[$(SKUId).val()]);

                    }
                    else {
                        $(qtyId).val(0);
                        if (Name === 'Product') availabilitiesTab[$(productId).val()] -= parseInt(precQty);
                        qtyTotal -= parseInt(precQty);
                        // availabilitiesTab[$(SKUId).val()] = Math.abs(availabilitiesTab[$(SKUId).val()]);
                        availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                        $('#itemsqtytotal').text(Math.abs(qtyTotal));
                    }
                }
                else {

                    $(qtyId).val(0);
                    if (Name === 'Product') {
                        availabilitiesTab[$(productId).val()] -= parseFloat(precQty);
                        // availabilitiesTab[$(SKUId).val()] = Math.abs(availabilitiesTab[$(SKUId).val()]);
                        availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                    }
                    qtyTotal -= parseFloat(precQty);
                    $('#itemsqtytotal').text(Math.abs(qtyTotal));
                }
            }
            else {
                $(qtyId).val(0);
                if (Name === 'Product') {
                    availabilitiesTab[$(productId).val()] = availabilitiesTab[$(productId).val()] + parseInt(precQty);
                    // availabilitiesTab[$(SKUId).val()] = Math.abs(availabilitiesTab[$(SKUId).val()]);
                    availabilitiesTab[$(productId).val()] = Math.abs(availabilitiesTab[$(productId).val()]);
                }
                qtyTotal = qtyTotal - parseInt(precQty);
                $('#itemsqtytotal').text(Math.abs(qtyTotal));
            }

            if (Name === 'Product') $(available).val(+availabilitiesTab[$(productId).val()]);
            tabQty[index] = parseInt($(qtyId).val());
            tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
            var Str = String($(priceId).val());
            //console.log('Price value = ' + $(priceId).val());
            var Name = $(priceId + ' option[value=\"' + Str + '\"]').text();
            var priceId_ = String(Name);
            //console.log('Option selected : ' + String(Name));
            $(amountId).val(parseFloat(priceId_) * parseInt($(qtyId).val()));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(priceId_, qtyId, index);
            precQty = $(qtyId).val();
            console.log('new precQty = ' + parseInt(precQty));
            //console.log('precQty = ' + precQty);
        });

        /*const orderItemsCounter_ = +$('#order_orderItems div.nbItems').length;
        console.log('orderItemsCounter_ = ' + orderItemsCounter_);
        for (let index_ = 0; index_ < orderItemsCounter_; index_++) {
            var SKUId_ = '#order_orderItems_' + index_ + '_sku';
            var productId_ = '#order_orderItems_' + index_ + '_product';
            $(SKUId_ + " option").each(function () {
                //console.log($(this).html());
                $(this).removeClass('d-none');
            });
            var Str_ = String($(SKUId).val());
            tabHideSKU.push(Str_);
            //var Name_ = $(SKUId_ + ' option[value=\"' + Str_ + '\"]').text();
            tabHideSKU.forEach(element => {
                $(SKUId_ + " option[value='" + element + "']").attr('selected', '');
                $(SKUId_ + " option[value='" + element + "']").addClass('d-none');
                $(productId_ + " option[value='" + element + "']").attr('selected', '');
                $(productId_ + " option[value='" + element + "']").addClass('d-none');

            });


        }*/

        handleDeleteButton();
    });
    //console.log($('#order_orderItems').html());
    console.log('widgets-count = ' + $('#orderItems-widgets-count').val());
    $('#commercial_sheet_orderItems_' + index + '_quantity').attr('type', 'number');

    $('.colpriceView_commercial_sheet_orderItems_' + index).removeClass('d-none');
    $('.col3_commercial_sheet_orderItems_' + index).addClass('d-none');

    // tabSKUIds[index] = '#order_orderItems_' + index + '_sku';
    // tabProductIds[index] = '#order_orderItems_' + index + '_product';
    // tabPriceIds[index] = '#order_orderItems_' + index + '_price';
    // tabProductQtyIds[index] = '#order_orderItems_' + index + '_quantity';

});

$('#add-servItems').click(function () {
    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#orderItems-widgets-count').val();
    console.log(index);
    $('#orderItems-widgets-count').val(index + 1);

    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#commercial_sheet_orderItems').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //Initialisation du facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items relatif 
    //à la qty de cet item
    tabError[index] = parseInt($('#orderItems-widgets-count').val()) - index;
    console.log('tabError[' + index + '] = ' + tabError[index]);
    //MAJ des facteur multiplicatif du compensateur de l'erreur sur la quantité total d'items des items à 
    //index inférieur à l'item créé
    for (let i = 0; i < index; i++) {
        tabError[i] = parseInt($('#orderItems-widgets-count').val()) - i;
        tabQtyError[i] = (tabError[i] - 1) * tabQty[i];
        console.log('tabError[' + i + '] = ' + tabError[i]);
        console.log('tabQtyError[' + i + '] = ' + tabQtyError[i]);
    }
    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    $('#commercial_sheet_orderItems').append(tmpl).ready(() => {
        // var SKUId = '#commercial_sheet_orderItems_' + index + '_sku';
        //var productId = '#commercial_sheet_orderItems_' + index + '_product';
        var offerInId = '#commercial_sheet_orderItems_' + index + '_offerIn';
        //var priceId = '#commercial_sheet_orderItems_' + index + '_price';
        var priceInId = '#commercial_sheet_orderItems_' + index + '_priceIn';
        var qtyId = '#commercial_sheet_orderItems_' + index + '_quantity';
        var amountId = '#commercial_sheet_orderItems_' + index + '_amount';
        //var available = '#commercial_sheet_orderItems_' + index + '_available';
        var offerTypeInId = '#commercial_sheet_orderItems_' + index + '_offerTypeIn';

        $(offerTypeInId).val('Service');

        tabOfferTypeIn[index] = $(offerTypeInId).val();
        //tabSKUIds[index] = $(SKUId).val();
        //tabProductIds[index] = $(productId).val();
        if (isNaN(parseFloat($(priceInId).val())) || parseFloat($(priceInId).val()) < 0 || !$(qpriceInId).val() == true) {
            $(priceInId).val(0.0);
        }
        if (isNaN(parseFloat($(qtyId).val())) || parseFloat($(qtyId).val()) < 0 || !$(qtyId).val() == true) {
            $(qtyId).val(0);
        }
        var precQty = parseInt($(qtyId).val());
        qtyTotal += parseInt($(qtyId).val());
        tabQty[index] = parseInt($(qtyId).val());
        tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
        $(amountId).val(parseFloat($(priceInId).val()) * parseInt($(qtyId).val()));
        //Calcul du montant relatif à ce produit
        computeItemsAmountTab(parseFloat($(priceInId).val()), qtyId, index);

        //Gestion des évènements de modification des entrées de l'order item ajouté

        $(priceInId).change(() => {
            //$(priceId).val($(productId).val());

            //tabSKUIds[index] = $(SKUId).val();
            //tabProductIds[index] = $(productId).val();

            if (isNaN(parseFloat($(this).val())) || parseFloat($(this).val()) < 0 || !$(this).val() == true) {
                $(this).val(0.0);
            }
            $(amountId).val(parseFloat($(priceInId).val()) * parseInt($(qtyId).val()));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(parseFloat($(priceInId).val()), qtyId, index);
        });

        //Gestion de l'évènement de changement de vleur de la quantité du produit 
        //pour mettre à jour les montants
        $(qtyId).change(() => {
            var add = false;
            var diff = 0;
            if (!$(qtyId).val() == false) {
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


            tabQty[index] = parseInt($(qtyId).val());
            tabQtyError[index] = (tabError[index] - 1) * tabQty[index];
            $(amountId).val(parseFloat($(priceInId).val()) * parseInt($(qtyId).val()));
            //Calcul du montant relatif à ce produit
            computeItemsAmountTab(parseFloat($(priceInId).val()), qtyId, index);
            precQty = parseInt($(qtyId).val());
            //console.log('precQty = ' + precQty);
        });

        handleDeleteButton();
    });
    //console.log($('#order_orderItems').html());
    //handleDeleteButton();
    $('#commercial_sheet_orderItems_' + index + '_quantity').attr('type', 'number');
    $('#commercial_sheet_orderItems_' + index + '_priceIn').attr('type', 'number');

    $('#commercial_sheet_orderItems_' + index + '_product').attr('required', false);
    $('#commercial_sheet_orderItems_' + index + '_price').attr('required', false);
    $('#commercial_sheet_orderItems_' + index + '_offerIn').attr('required', true);
    $('#commercial_sheet_orderItems_' + index + '_priceIn').attr('required', true);

    $('.colavai_commercial_sheet_orderItems_' + index).addClass('d-none');
    $('.col1_commercial_sheet_orderItems_' + index).addClass('d-none');
    $('.col3_commercial_sheet_orderItems_' + index).addClass('d-none');
    $('.col2_commercial_sheet_orderItems_' + index).removeClass('d-none');
    $('.col4_commercial_sheet_orderItems_' + index).removeClass('d-none');
    // tabSKUIds[index] = '#order_orderItems_' + index + '_sku';
    // tabProductIds[index] = '#order_orderItems_' + index + '_product';
    // tabPriceIds[index] = '#order_orderItems_' + index + '_price';
    // tabProductQtyIds[index] = '#order_orderItems_' + index + '_quantity';

});

handleDeleteButton();
updateCounter();

//Calcul du montant total de la promo
computeTotalPromoAmount();

function updateCounter() {
    const orderItemsCounter = +$('#commercial_sheet_orderItems div.nbItems').length;
    //const reductionsCounter = +$('#commercial_sheet_reductions div.form-group').length;

    $('#orderItems-widgets-count').val(orderItemsCounter);
    //$('#reductions-widgets-count').val(reductionsCounter);
    //console.log('widgets-count = ' + $('#orderItems-widgets-count').val());
}

function handleDeleteButton() {
    //Je gère l'action du click sur les boutton possédant l'attribut data-action = "delete"
    $('button[data-action="delete"]').click(function () {
        //Je récupère l'identifiant de la cible(target) à supprimer en recherchant 
        //dans les attributs data-[quelque chose](grâce à dataset) celui dont quelque chose = target (grâce à target)
        const target = this.dataset.target;
        var str = String(target);
        var subItems = String('_orderItems_');
        var posItems = str.indexOf(subItems);
        //var subReductions = String('_reductions_');
        // var posReductions = str.indexOf(subReductions);
        var index = 0;
        if (posItems > 0) {
            //console.log(str.substr(posItems + subItems.length));
            index = parseInt(str.substr(posItems + subItems.length));
            //console.log('subItems index = ' + index);
            // itemsAmount.splice(index, 1);
            itemsAmount[index] = 0;
            if (tabOfferTypeIn[index] === 'Product') {
                //tabHideSKU.splice(index, 1);
                tabHideProduct.splice(index, 1);
                //console.log(itemsAmount);
                //availabilitiesTab[tabSKUIds[index]] = availabilities[tabSKUIds[index]];
                availabilitiesTab[tabProductIds[index]] = availabilities[tabProductIds[index]];
            }
            console.log('index = ' + index);
            console.log('before Qty total = ' + qtyTotal);
            console.log('tabQtyError[' + index + '] = ' + tabQtyError[index]);
            qtyTotal = qtyTotal - parseInt(tabQty[index]) + tabQtyError[index];
            $('#itemsqtytotal').text(Math.abs(qtyTotal));
            console.log('after Qty total = ' + qtyTotal);
            tabQtyError[index] = 0;

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

function computeItemsAmountTab(priceId, qtyId, index) {
    var price = parseFloat(priceId);
    var amount = price * parseFloat($(qtyId).val());
    //$(amountId).val(amount);
    itemsAmount[index] = amount;
    //console.log('itemsAmount[' + index + '] = ' + itemsAmount[index]);

    //Calcul du sous montant Total des items
    computeItemsAmountSubTotal();
}

//Procédure de calcul du sous montant Total des items
function computeItemsAmountSubTotal() {
    itemsAmountSubTotal = 0;
    itemsAmount.forEach(function (item, index) {
        itemsAmountSubTotal += item;
    });
    //console.log(itemsAmountSubTotal);
    $('#itemsAmountSubtotal').text(itemsAmountSubTotal);

    //MAJ du montant de réduction sur la commande
    itemsReduction = (parseFloat($('#commercial_sheet_itemsReduction').val()) / 100) * itemsAmountSubTotal;

    //Calcul du montant total de la promo
    computeTotalPromoAmount();

}

//Procédure de calcul de la réduction relative à ce pot
/*function computeReductionsAmountTab(priceId, qtyId, index) {
    var Str = String($(priceId).val());
    //console.log('Price value = ' + $(priceId).val());
    var Name = $(priceId + ' option[value=\"' + Str + '\"]').text();
    //console.log('Option selected : ' + String(Name));
    var price = parseFloat(String(Name));
    reductionsAmount[index] = price * $(qtyId).val();
    //console.log('reductionsAmount[' + index + '] = ' + reductionsAmount[index]);

    //Calcul de la réduction Total sur les pots retournés
    computeReductionsAmountSubtotal();
}*/

//Procédure de calcul de la réduction Total sur les pots retournés
/*function computeReductionsAmountSubtotal() {
    reductionsAmountSubtotal = 0;
    reductionsAmount.forEach(function (item, index) {
        reductionsAmountSubtotal += item;
    });
    //console.log(reductionsAmountSubtotal);

    //Calcul du montant total de la promo
    computeTotalPromoAmount();
}*/

//Procédure de calcul du montant total de la promo
function computeTotalPromoAmount() {
    totalPromoAmount = itemsReduction + fixReductions;
    $('#totalPromoAmount').text(totalPromoAmount);

    //Calcul du montant Total Net à payer
    computeTotalAmount();
}

//Procédure de calcul du montant Total Net à payer
function computeTotalAmount() {
    totalAmount = itemsAmountSubTotal + deliveryFees - totalPromoAmount;
    $('#totalAmount').text(totalAmount);
}
