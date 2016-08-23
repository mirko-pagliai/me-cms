<?php
    $this->assign('title', $page->title = 'Cookies policy');
    ob_start();
?>

<p>Effective date: Genuary 28, 2016.</p>

<h2>What are cookies?</h2>
<p>
    Cookies are small text files placed onto your computer or device as you 
    browse the internet. Cookies can be used to collect, store and share bits 
    of information about your activities across websites, including our site. 
    They also allow us to remember things about your visit our site, such as 
    your preferred language and other choices/settings and generally make the 
    site easier for you to use.
</p>
<p>
    We use both session cookies and persistent cookies. A session cookie is 
    used to identify a particular visit to our site. These cookies expire 
    after a short time, or when you close your web browser after using our 
    services. We use these cookies to identify you during a single browsing 
    session, such as when you log into our site. A persistent cookie will 
    remain on your devices for a set period of time specified in the cookie. 
    We use these cookies where we need to identify you over a longer period 
    of time. For example, we would use a persistent cookie if you asked that 
    we keep you signed in.
</p>

<h2>Why do you use cookies and similar technologies?</h2>
<p>
    We use cookies and similar technologies to deliver, measure, and improve 
    our services in various ways.
</p>
<p>
    We use cookies for the following purposes:
</p>

<h3>1. Authentication and security</h3>
<ul>
    <li>To log you into our site;</li>
    <li>to protect your security;</li>
    <li>
        to help us detect and fight spam, abuse and other illicit activities.
    </li>
</ul>
<p>
    For example, these technologies help authenticate your access to our site 
    and prevent unauthorised parties from accessing your account. They also 
    let us show you appropriate content through our services.
</p>

<h3>2. Preferences</h3>
<ul>
    <li>To remember information about your browser and your preferences;</li>
    <li>to remember your settings and other choices you have made.</li>
</ul>
<p>
    For example, cookies help us remember your preferred language or the 
    country that you are in. We can then provide you with text content in your 
    preferred language without having to ask you each time you visit our site.
</p>

<h3>3. Analytics and research</h3>
<ul>
    <li>To help us improve and understand how people use our services.</li>
</ul>
<p>
    For example, cookies help us test different versions of our services to 
    see which particular features or content users prefer. We may include web 
    beacons in e-mail messages or newsletters to determine whether the 
    message has been opened and for other analytics. We might also optimise 
    and improve your experience on our site by using cookies to see how you 
    interact with our services.
</p>
<p>
    To help us better understand how people use our services, we work with a 
    number of analytics partners, including Google Analytics.
</p>
<p>
    To find out more about their cookies and the privacy choices they offer, 
    please visit the following links: 
    <a href="http://www.google.com/intl/en/policies/privacy" target="_blank">Google Analytics</a>.
</p>

<h3>4. Personalised content</h3>
<ul>
    <li>To customise our services with more relevant content.</li>
</ul>

<h3>5. Advertising</h3>
<ul>
    <li>To provide you with more relevant advertising.</li>
</ul>
<p>
    Third-parties whose products or services are accessible or advertised via 
    the services may use cookies to collect information about your activities 
    on the services, other sites, and/or the ads you have clicked on. This 
    information may be used by them to serve ads that they believe are most 
    likely to be of interest to you and measure the effectiveness of their ads.
</p>
<p>
    For more information about targeting and advertising cookies and how you 
    can opt out, you can visit 
    <a href="http://youronlinechoices.eu" target="_blank">youronlinechoices.eu</a> 
    or 
    <a href="http://www.allaboutcookies.org/manage-cookies/index.html" target="_blank">allaboutcookies.org</a>.
</p>
<p>
    When accessing our site from a mobile application you may also receive 
    tailored in-application advertisements. Each operating system (iOS, 
    Android and Windows Phone) provides its own instructions on how to 
    prevent the delivery of tailored in-application advertisements. You may 
    review the support materials and/or the privacy settings for the 
    respective operating systems in order to opt-out of tailored 
    in-application advertisements. For any other devices and/or operating 
    systems, please visit the privacy settings for the applicable device or 
    operating system or contact the applicable platform operator.
</p>

<h3>6. Social networks and sharing</h3>
<ul>
    <li>
        To show buttons and social widget by some social networks, or 
        services of interaction with social networks.
    </li>
</ul>
<p>
    These services allow you to make interactions with social networks or 
    other external platforms directly from the pages of our site and our 
    applications.
</p>
<p>
    Interactions and information that we acquire are in any case subject to 
    the user's privacy settings related to any social network. When there 
    are services of interaction with social networks, it is possible that, 
    even if users do not use them, the same collect traffic data about the 
    pages in which it is installed.
</p>

<h2>Where are cookies and similar technologies used?</h2>
<p>
    We use these technologies on our websites, apps and services.
</p>
<p>
    We don't release the information collected from our own cookies to any 
    third parties, other than to our service providers who assist us in 
    providing the services.
</p>

<h2>What are my privacy options?</h2>
<p>
    When you first access the site, it will let you know that we use 
    cookies - by continuing to use or access the site, you are consenting to 
    our use of cookies and related technologies as described in this policy.
</p>
<p>
    Most browsers automatically accept cookies, but you can modify your 
    browser setting to limit or decline cookies by visiting your browser's 
    help page. If you choose to decline cookies, please note that you may 
    not be able to sign in, customize, or use some features of our services.
</p>
<p>
    For general information about cookies and how to disable them, please visit 
    <a href="http://www.allaboutcookies.org" target="_blank">allaboutcookies.org</a>.
</p>

<h2>Changes to this policy</h2>
<p>
    We will notify you of any changes by posting the new policy with a new 
    effective date. If we make a material change to our cookie policy, we 
    will take reasonable steps to notify you in advance of the planned change.
</p>

<h2>Questions</h2>
<p>
    If you have any questions about our use of cookies, please click here.
</p>

<?php
    $page->text = ob_get_clean();
    echo $this->Html->div(
        'pages view',
        $this->element('views/page', compact('page'))
    );
    