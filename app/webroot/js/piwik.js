var pkBaseURL = (("https:" == document.location.protocol) ? "https://livestats.noc.teiath.gr/piwik/" : "http://livestats.noc.teiath.gr/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
try {
    var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 13);
    piwikTracker.trackPageView();
    piwikTracker.enableLinkTracking();
} catch( err ) {}
