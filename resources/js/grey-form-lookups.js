function syncGreyLookup(select) {
    const target = document.querySelector(select.dataset.greyLookupTarget || select.dataset.greyLineQualityName);
    if (!target) {
        return;
    }
    const option = select.selectedOptions[0];
    target.value = option?.dataset.name ?? '';
}

document.querySelectorAll('[data-grey-lookup], [data-grey-quality-lookup]').forEach((select) => {
    const handler = () => syncGreyLookup(select);
    select.addEventListener('change', handler);
    handler();
});
