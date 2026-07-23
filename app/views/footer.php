    <footer class="footer">
        <span></span>
        <span></span>
    </footer>
</main>
<script>
function printPdfSilent(url) {
    let iframe = document.getElementById('print-iframe-hidden');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-iframe-hidden';
        iframe.style.visibility = 'hidden';
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }
    iframe.src = url;
}
</script>
</body>
</html>

