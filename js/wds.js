var cssId = 'people-list-style-wds';  // you could encode the css path itself to generate id..
if ( ! document.getElementById( cssId ) )
{
    var head  = document.getElementsByTagName('head')[0];
    var link  = document.createElement('link');
    link.id   = cssId;
    link.rel  = 'stylesheet';
    link.type = 'text/css';
    link.href = 'https://cdn.web.wsu.edu/designsystem/2.x/dist/bundles/standalone/people-list/styles.css?version=1.0.0';
    link.media = 'all';
    head.appendChild(link);
}