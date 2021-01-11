<footer class="website-footer">
    <p><b>Note:</b> Please configure Docsgo IM using this <a href="/openfire/docsgoIM_config_document.pdf"><b
                >configuration document</b></a> so as to get real time update of your documents and reviews.</p>
</footer>
</main>

<script>
$(document).ready(function() {

    $('textarea').each(function() {
        var simplemde = new SimpleMDE({
            element: this,
            status: false,
            spellChecker: true,
            // status: [{
            //          className: "characters",
            //          defaultValue: function(el) {
            //             el.innerHTML = "0";
            //          },
            //          onUpdate: function(el) {
            //             el.innerHTML = simplemde.value().length;
            //          }
            //       }],
            showIcons: ["code", "table"],
            hideIcons: ["guide"]
        });
        simplemde.codemirror.refresh();

        simplemde.codemirror.on("update", function(el) {
            const updatedValue = el.getValue()
            var textarea = el.getTextArea();
            textarea.innerHTML = updatedValue;
        });
    });

});

function goBack() {
    window.history.back();
}
</script>

</body>

</html>