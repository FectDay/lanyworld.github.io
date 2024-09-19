select.addEventListener('change', changeURLLanguage);

function changeURLLanguage(){
    let lang = select.vaule;
    location.href = window.location.pathname + '/' + lang;
    location.reload()
}
