var tabHideCategory = [];
var tabCategoryIds = [];


$('#add-categories').click(function () {
    //Je récupère le numéro du futur champ que je vais créer
    const index = +$('#categories-widgets-count').val();
    //console.log(index);
    $('#categories-widgets-count').val(index + 1);

    //$('#add-categories').attr('disabled', true);
    //Je récupère le prototype des entrées(champs) et je remplace dans ce
    //prototype toutes les expressions régulières (drapeau g) "___name___" (/___name___/) par l'index
    const tmpl = $('#product_categories').data('prototype').replace(/__name__/g, index);
    //console.log(tmpl);

    //J'ajoute à la suite de la div contenant le sous-formulaire ce code
    $('#product_categories').append(tmpl).ready(() => {
        var categoriesId = '#product_categories_' + index + '_name';

        //Ajout de l'option fictive de gestion des options à retire des autres select list
        // var Opt = new Option("Select a Category", "-1");
        // $(categoriesId).append(Opt);
        // $(categoriesId).val("-1");

        tabHideCategory.forEach(function (value, index_) {
            $(categoriesId + " option[value='" + value + "']").remove();

        });
        tabCategoryIds[index] = $(categoriesId).val();

        $(categoriesId).change(() => {

            //Retrait des options fictives précédemment ajoutées
            $(categoriesId + " option[value='-1']").remove();

            var Str = String($(categoriesId).val());
            //console.log('Category  value = ' + $(categoriesId).val());
            var Name = $(categoriesId + ' option[value=\"' + Str + '\"]').text();
            // var puId_ = String(Name);
            // $(designationId).val(puId_);

            //tabQtyError[index] = (tabError[index] - 1) * tabQty[index];

            $('#add-categories').attr('disabled', false);
            tabHideCategory.forEach(function (value, index_) {
                //console.log('in foreach tabHideCategory ' + index_ + ' : value = ' + value);
                // var categoriesSKUId_ = '#categories_categories_' + index_ + '_categoriesSku';
                var categoriesId_ = '#product_categories_' + index_ + '_categories';
                // var categoriesPriceId_ = '#product_categories_' + index_ + '_categoriesPrice';
                $(categoriesId_ + " option[value='" + $(categoriesId).val() + "']").remove();

            });

        });

        handleDeleteButton();
    });


});


handleDeleteButton();
updateCounter();

function updateCounter() {
    const categoriesCounter = +$('#product_categories div.nbItems').length;
    //const reductionsCounter = +$('#product_reductions div.form-group').length;

    $('#categories-widgets-count').val(categoriesCounter);
    //$('#reductions-widgets-count').val(reductionsCounter);
    //console.log('widgets-count = ' + $('#categories-widgets-count').val());
}

function handleDeleteButton() {
    //Je gère l'action du click sur les boutton possédant l'attribut data-action = "delete"
    $('button[data-action="delete"]').click(function () {
        //Je récupère l'identifiant de la cible(target) à supprimer en recherchant 
        //dans les attributs data-[quelque chose](grâce à dataset) celui dont quelque chose = target (grâce à target)
        const target = this.dataset.target;
        var str = String(target);
        var subItems = String('_categories_');
        var posItems = str.indexOf(subItems);

        var index = 0;
        if (posItems > 0) {
            //console.log(str.substr(posItems + subItems.length));
            index = parseInt(str.substr(posItems + subItems.length));

            tabHideCategory[index] = '';


        }

        //console.log(target);
        $(target).remove();
        //updateCounter();
    });
};
