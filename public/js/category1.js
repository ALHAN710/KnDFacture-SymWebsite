var tabHideProduct = [];
var tabProductIds = [];


$('#add-products').click(function () {
    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#products-widgets-count').val();
    //console.log(index);
    $('#products-widgets-count').val(index + 1);

    //$('#add-products').attr('disabled', true);
    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#category_products').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    $('#category_products').append(tmpl).ready(() => {
        //var productsId = '#category_products_' + index + '_name';
        var nameId = '#category_products_' + index + '_name';
        var priceId = '#category_products_' + index + '_price';
        var skuId = '#category_products_' + index + '_sku';
        // var descriptionId = '#category_products_' + index + '_description';
        // var hasStockId = '#category_products_' + index + '_hasStock';

        var productNameId = '#category_products_' + index + '_productName';
        var productPriceId = '#category_products_' + index + '_productPrice';
        var productSkuId = '#category_products_' + index + '_productSku';
        // var productDescriptionId = '#category_products_' + index + '_productDescription';
        // var productHasStockId = '#category_products_' + index + '_productHasStock';

        $(productNameId).select2({
            width: '100%',
            //height: '300%',
            //dropdownCssClass: "custom-select"
        });

        //Ajout de l'option fictive de gestion des options à retire des autres select list
        // var Opt = new Option("Select a Product", "-1");
        // $(productsId).append(Opt);
        // $(productsId).val("-1");

        /*tabHideProduct.forEach(function (value, index_) {
            $(productsId + " option[value='" + value + "']").remove();

        });
        tabProductIds[index] = $(productsId).val();*/

        /*$(nameId).change(() => {

            //Retrait des options fictives précédemment ajoutées
            $(productsId + " option[value='-1']").remove();

            var Str = String($(productsId).val());
            //console.log('Product  value = ' + $(productsId).val());
            var Name = $(productsId + ' option[value=\"' + Str + '\"]').text();
            // var puId_ = String(Name);
            // $(designationId).val(puId_);

            //tabQtyError[index] = (tabError[index] - 1) * tabQty[index];

            $('#add-products').attr('disabled', false);
            tabHideProduct.forEach(function (value, index_) {
                //console.log('in foreach tabHideProduct ' + index_ + ' : value = ' + value);
                // var productsSKUId_ = '#products_products_' + index_ + '_productsSku';
                var productsId_ = '#category_products_' + index_ + '_products';
                // var productsPriceId_ = '#category_products_' + index_ + '_productsPrice';
                $(productsId_ + " option[value='" + $(productsId).val() + "']").remove();

            });

        });*/

        $(productNameId).change(() => {

            $(productPriceId).val($(productNameId).val());
            $(productSkuId).val($(productNameId).val());
            // $(productDescriptionId).val($(productNameId).val());
            // $(productHasStockId).val($(productNameId).val());
            //tabQtyError[index] = (tabError[index] - 1) * tabQty[index];

            var Str = String($(productNameId).val());
            //console.log('Product  value = ' + $(productsId).val());
            var Name = $(productNameId + ' option[value=\"' + Str + '\"]').text();
            var puId_ = String(Name);
            $(nameId).val(puId_);

            Str = String($(productPriceId).val());
            //console.log('Product  value = ' + $(productsId).val());
            Name = $(productPriceId + ' option[value=\"' + Str + '\"]').text();
            puId_ = String(Name);
            $(priceId).val(puId_);

            Str = String($(productSkuId).val());
            //console.log('Product  value = ' + $(productsId).val());
            Name = $(productSkuId + ' option[value=\"' + Str + '\"]').text();
            puId_ = String(Name);
            $(skuId).val(puId_);

            // Str = String($(productDescriptionId).val());
            // //console.log('Product  value = ' + $(productsId).val());
            // Name = $(productDescriptionId + ' option[value=\"' + Str + '\"]').text();
            // puId_ = String(Name);
            // $(descriptionId).val(puId_);

            // Str = String($(productDescriptionId).val());
            // //console.log('Product  value = ' + $(productsId).val());
            // Name = $(productDescriptionId + ' option[value=\"' + Str + '\"]').text();
            // puId_ = String(Name);
            // $(descriptionId).val(puId_);

            // Str = String($(productHasStockId).val());
            // //console.log('Product  value = ' + $(productsId).val());
            // Name = $(productHasStockId + ' option[value=\"' + Str + '\"]').text();
            // puId_ = String(Name);
            // $(hasStockId).val(puId_);

            //$('#add-products').attr('disabled', false);
            /*tabHideProduct.forEach(function (value, index_) {
                //console.log('in foreach tabHideProduct ' + index_ + ' : value = ' + value);
                // var productsSKUId_ = '#products_products_' + index_ + '_productsSku';
                var productsId_ = '#category_products_' + index_ + '_products';
                // var productsPriceId_ = '#category_products_' + index_ + '_productsPrice';
                $(productsId_ + " option[value='" + $(productsId).val() + "']").remove();

            });*/

        });

        handleDeleteButton();
    });


});


handleDeleteButton();
updateCounter();

function updateCounter() {
    const productsCounter = +$('#category_products div.nbItems').length;
    //const reductionsCounter = +$('#product_reductions div.form-group').length;

    $('#products-widgets-count').val(productsCounter);
    //$('#reductions-widgets-count').val(reductionsCounter);
    //console.log('widgets-count = ' + $('#products-widgets-count').val());
}

function handleDeleteButton() {
    //Je gère l'action du click sur les boutton possédant l'attribut data-action = "delete"
    $('button[data-action="delete"]').click(function () {
        //Je récupère l'identifiant de la cible(target) à supprimer en recherchant 
        //dans les attributs data-[quelque chose](grâce à dataset) celui dont quelque chose = target (grâce à target)
        const target = this.dataset.target;
        var str = String(target);
        var subItems = String('_products_');
        var posItems = str.indexOf(subItems);

        var index = 0;
        if (posItems > 0) {
            //console.log(str.substr(posItems + subItems.length));
            index = parseInt(str.substr(posItems + subItems.length));

            tabHideProduct[index] = '';


        }

        //console.log(target);
        $(target).remove();
        //updateCounter();
    });
};
