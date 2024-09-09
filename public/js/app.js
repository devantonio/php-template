document.addEventListener('DOMContentLoaded', () => {
    const _$ = document.querySelector.bind(document);
    const _$$ = document.querySelectorAll.bind(document);

    _$('body').onclick = (e) => {
        console.log("clicked");
        if(e.target && e.target.classList.contains('overlay')) {}
    }
});