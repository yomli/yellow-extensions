<?php
// Basic extension, https://github.com/schulle4u/yellow-extension-basic

class YellowYomli {
    const VERSION = "0.8.15";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("yomliFeedLocation", "/feed/");
        $this->yellow->system->setDefault("yomliFeedTitle", "RSS feed: Articles");
        $this->yellow->system->setDefault("yomliFeedFileXml", "articles.xml");
        $this->yellow->system->setDefault("yomliFeedFilterLayout", "blog");
        $this->yellow->system->setDefault("yomliFeedPaginationLimit", "30");
    }

    //==================================
    // HANDLE MICROTYPOGRAPHY         //
    //==================================
    public function onParseContentHtml($page, $text) {

        // Locale dependant
        // $ordinalSuffixes = "st|nd|rd|th|o|er|ers|re|res|e|es|è|ere|ère|d|nd|nde|de|ds|des|me|eme|ème";
        $ordinals = array(  "er" => "1|I" ,
                            "ers" => "1|I" ,
                            "re" => "1|I" ,
                            "res" => "1|I" ,
                            "d" => "2|II",
                            "ds" => "2|II",
                            "de" => "2|II",
                            "des" => "2|II",
                            "st" => "1|I",
                            "nd" => "2|II",
                            "rd" => "3|III");
        $ordinalSuffixes = "e|es|th";
        $abbrPrefixes = "M|V|D|P|S|L|C";
        $abbrSuffixes = "me|mes|lle|lles|ve|r|rs|gr|t|te|tesse|ne|dt";
        $validRomans = "((?=(?:I|V|X|[I|V|X|L|C|D|M]{2,}))(?=[I|V|X|L|C|D|M])M*(?:C[MD]|D?C*)(?:X[CL]|L?X*)(?:I[XV]|V?I*))";
        $fractions = array( "1/2" => "½",
                            "1/3" => "⅓",
                            "1/4" => "¼",
                            "1/5" => "⅕",
                            "1/6" => "⅙",
                            "1/7" => "⅐",
                            "1/8" => "⅛",
                            "1/9" => "⅑",
                            "2/3" => "⅔",
                            "2/5" => "⅖",
                            "3/4" => "¾",
                            "3/5" => "⅗",
                            "3/8" => "⅜",
                            "4/5" => "⅘",
                            "5/6" => "⅚",
                            "5/8" => "⅝",
                            "7/8" => "⅞");

        $tagsToSkip = "pre|code|kbd|samp|script|style|math";
        $regexSkip = "(?:(?:(?s)<(?:".$tagsToSkip.")[^<]*>.*?<\/(?:".$tagsToSkip.")>)|(?:<.*?>))(*SKIP)(*F)|";

        $output = $text;

        // Small caps handle
        $output = preg_replace('~'.$regexSkip.'\[(.*?)\]{\.(smallcaps|small-caps|all-small-caps)}~', '<span class="$2">$1</span>', $output);

        // Lang handle
        $output = preg_replace('~'.$regexSkip.'\[(.*?)\]{lang="(.*?)"}~', '<em lang="$2">$1</em>', $output);

        // French guillemets
        $output = preg_replace('~'.$regexSkip.'«\s(.*?)\s»~', '«&nbsp;$1&nbsp;»', $output);

        // Curly quotes
        $output = preg_replace('~'.$regexSkip.'"(.*?)"~', '“$1”', $output);

        // Apostrophe
        $output = preg_replace('~'.$regexSkip.'(\pL)\'~', '$1’', $output);

        // French double ponctuation
        $output = preg_replace('~'.$regexSkip.'\s(\:|\;|\?|\!)~', '&#8239;$1', $output);

        // Replace the simple - by a ndash between numbers (dates ranges...)
        // Adapted from https://github.com/jolicode/JoliTypo/
        $output = preg_replace('~'.$regexSkip.'(?<=[0-9 ]|^)-(?=[0-9 ]|$)~', '–', $output);

        // No space before comma
        // Adapted from https://github.com/jolicode/JoliTypo/
        $output = preg_replace('~'.$regexSkip.'([^\d\s]+)[\s]*(,)[\s]*~', '$1$2 ', $output);

        // Adds a nobreak space before an unit
        // Adapted from https://github.com/jolicode/JoliTypo/
        $output = preg_replace('~'.$regexSkip.'([\dº])(\s)+([º°%Ω฿₵¢₡$₫֏€ƒ₲₴₭£₤₺₦₨₱៛₹$₪৳₸₮₩¥µ]|(\w(\s|\.|,|;|!|\?))|[\dº]{1})~', '$1&nbsp;$3', $output);

        // Replace x and - symbols in a mathematical context
        // Adapted from https://github.com/jolicode/JoliTypo/
        $output = preg_replace('~'.$regexSkip.'(\d+["\']?)(\s)?x(\s)?(?=\d)~', '$1$2×$2', $output);
        $output = preg_replace('~'.$regexSkip.'(\d+["\']?)(\s)?-(\s)?(?=\d)~', '$1$2−$2', $output);

        // Converts (c) (tm) and (r) to ASCII equivalents
        // Adapted from https://github.com/jolicode/JoliTypo/
        $output = preg_replace('~'.$regexSkip.'\(tm\)~', '™', $output);
        $output = preg_replace('~'.$regexSkip.'\(c\)[\s]([0-9]+)~', '©&nbsp;$1', $output);
        $output = preg_replace('~'.$regexSkip.'\(c\)~', '©', $output);
        $output = preg_replace('~'.$regexSkip.'\(r\)~', '®', $output);

        // Converts ordinals
        // Adapted from https://github.com/mundschenk-at/php-typography/
        // Arabic numerals
        // New from Yomli, avoid false positive like Id.
        foreach ($ordinals as $key => $value) {
            $output = preg_replace('~'.$regexSkip.'(?:\s)('.$value.')('.$key.')(?=\s|\.|,|;|!|\?)~', ' $1<sup>$2</sup>', $output);
        }
        // Arabic numerals
        $output = preg_replace('~'.$regexSkip.'(\d+)('.$ordinalSuffixes.')(?=\s|\.|,|;|!|\?)~', '$1<sup>$2</sup>', $output);
        // Roman numerals
        $output = preg_replace('~'.$regexSkip . $validRomans.'('.$ordinalSuffixes.')(?=\s|\.|,|;|!|\?)~', '$1<sup>$2</sup>', $output);

        // ... → …
        $output = preg_replace('~'.$regexSkip.'(\.{3,})~', '…', $output);

        // -- → —
        $output = preg_replace('~'.$regexSkip.'\s(--|---)\s~', '&nbsp;–&nbsp;', $output);
        $output = preg_replace('~'.$regexSkip.'\s(--|---)(?=\.|,|;|!|\?)~', '&nbsp;–', $output);

        // p. 10 → p.&nbsp;10
        // § 23 → §&nbsp;23
        // vol. 1 → vol.&nbsp;1
        $output = preg_replace('~'.$regexSkip.'(§|p\.|pp\.|vol\.)\s(\d+)~', '$1&nbsp;$2', $output);

        // etc.
        $output = preg_replace('~'.$regexSkip.'(\setc\.)~', '&nbsp;etc.', $output);

        // n° → n<sup>o</sup>
        $output = preg_replace('~'.$regexSkip.'n°(?:\s?)(\d+)~', 'n<sup>o</sup>&#8239;$1', $output);

        // Converts fractions like 1/2 into their UTF-8 equivalents ½
        foreach ($fractions as $key => $value) {
            $numerator = explode('/', $key)[0];
            $denominator = explode('/', $key)[1];
            $output = preg_replace('~'.$regexSkip.'(?<![\/\d])('.$numerator.')\/('.$denominator.')(?![\/\d])~', $value, $output);
        }
        $output = preg_replace('~'.$regexSkip.'~', '', $output);

        // Mme → M<sup>me</sup> and others
        $output = preg_replace('~'.$regexSkip.'('.$abbrPrefixes.')('.$abbrSuffixes.')(?:\s+)~', '$1<sup>$2</sup>&nbsp;', $output);

        // Il vaut mieux ne pas faire que faire à moitié
        // XXe siècle → <span class="all-small-caps">XX</span><sup>e</sup>&nbsp;siècle
        // Ier siècle → <span class="all-small-caps">I</span><sup>er</sup>&nbsp;siècle
        // Aux Ier, XIIe et XIIIe siècles -> Aux <span class="small-caps">I</span><sup>er</sup>, <span class="all-small-caps">XII</span><sup>e</sup> et XIIIe siècle
        // $output = preg_replace('~'.$regexSkip . $validRomans .'(e|<sup>e<\/sup>)(?:\s*)(siècle)~', '<span class="all-small-caps">$1</span><sup>e</sup>&nbsp;$3', $output);
        // $output = preg_replace('~'.$regexSkip . '(I{1})(er|<sup>er<\/sup>)(?:\s*)(siècle)~', '<span class="all-small-caps">$1</span><sup>er</sup>&nbsp;$3', $output);

        return $output;
    }

    //==================================
    // HANDLE FEED                    //
    //==================================

    // Handle page layout
    public function onParsePageLayout($page, $name) {
        if ($name=="feed") {
            $pages = $this->yellow->content->index(false, false);
            $pagesFilter = array();
            if ($page->isRequest("tag")) {
                $pages->filter("tag", $page->getRequest("tag"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("author")) {
                $pages->filter("author", $page->getRequest("author"));
                array_push($pagesFilter, $pages->getFilter());
            }
            $feedFilterLayout = $this->yellow->system->get("yomliFeedFilterLayout");
            if (!empty($feedFilterLayout)) $pages->filter("layout", $feedFilterLayout);
            $chronologicalOrder = ($this->yellow->system->get("yomliFeedFilterLayout")!="blog");
            if ($this->isRequestXml($page)) {
                $pages->sort($chronologicalOrder ? "modified" : "published", false);
                $pages->limit($this->yellow->system->get("yomliFeedPaginationLimit"));
                $title = !empty($pagesFilter) ? implode(" ", $pagesFilter)." - ".$this->yellow->page->get("sitename") : $this->yellow->page->get("sitename");
                $this->yellow->page->setLastModified($pages->getModified());
                $this->yellow->page->setHeader("Content-Type", "application/rss+xml; charset=utf-8");
                $urlBase = $this->yellow->page->scheme."://".$this->yellow->page->address.$this->yellow->page->base."/";
                $output = "<?xml version=\"1.0\" encoding=\"utf-8\"\077>\r\n";
                $output .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\r\n";
                $output .= "<channel>\r\n";
                $output .= "<title>".htmlspecialchars($title)."</title>\r\n";
                $output .= "<atom:link href=\"" . $this->yellow->page->getUrl() . $this->yellow->toolbox->normaliseArguments("page:" . $this->yellow->system->get("yomliFeedFileXml"), false) . "\" rel=\"self\" type=\"application/rss+xml\" />\r\n";
                $output .= "<link>".$urlBase."</link>\r\n";
                $output .= "<description>".$this->yellow->page->getHtml("description")."</description>\r\n";
                $output .= "<language>".$this->yellow->page->getHtml("language")."</language>\r\n";
                $output .= "<copyright>".$this->yellow->page->getHtml("author")."</copyright>\r\n";
                foreach ($pages as $pageFeed) {
                    $timestamp = strtotime($pageFeed->get($chronologicalOrder ? "modified" : "published"));
                    // Absolute path in content -_-
                    // DO NOT USE UNICODE IN RELATIVE URL
                    $content = $pageFeed->getContent();
                    $content = preg_replace('/src="(\/)([\pL\pM _()$&\'0-9\/!@^=+*-.]*)"/i', 'src="'.$this->yellow->page->scheme."://".$this->yellow->page->address.'/${2}"', $content);
                    $content = preg_replace('/href="(\/)([\pL\pM _()$&\'0-9\/!@^=+*-.]*)"/i', 'href="'.$this->yellow->page->scheme."://".$this->yellow->page->address.'/${2}"', $content);
                    
                    $output .= "<item>\r\n";
                    $output .= "<title>".$pageFeed->getHtml("title")."</title>\r\n";
                    $output .= "<link>".$pageFeed->getUrl()."</link>\r\n";
                    $output .= "<pubDate>".date(DATE_RSS, $timestamp)."</pubDate>\r\n";
                    $output .= "<guid isPermaLink=\"false\">".$pageFeed->getUrl()."?".$timestamp."</guid>\r\n";
                    $output .= "<dc:creator>".$pageFeed->getHtml("author")."</dc:creator>\r\n";
                    $output .= "<description><![CDATA[".$pageFeed->get("description")."]]></description>\r\n";
                    $output .= "<content:encoded><![CDATA[".$content."]]></content:encoded>\r\n";
                    if ($pageFeed->isExisting("tag")) {
                        foreach (preg_split("/\s*,\s*/", $pageFeed->get("tag")) as $tag) {
                            $output .= "<category>" . htmlspecialchars($tag) . "</category>\r\n";
                        }
                    }
                    $output .= "</item>\r\n";
                }
                $output .= "</channel>\r\n";
                $output .= "</rss>\r\n";
                $this->yellow->page->setOutput($output);
            } else {
                $pages->sort($chronologicalOrder ? "modified" : "published");
                $pages->pagination($this->yellow->system->get("yomliFeedPaginationLimit"));
                if (!$pages->getPaginationNumber()) $this->yellow->page->error(404);
                if (!empty($pagesFilter)) {
                    $text = implode(" ", $pagesFilter);
                    $this->yellow->page->set("titleHeader", $text." - ".$this->yellow->page->get("sitename"));
                    $this->yellow->page->set("titleContent", $this->yellow->page->get("title").": ".$text);
                    $this->yellow->page->set("title", $this->yellow->page->get("title").": ".$text);
                }
                $this->yellow->page->set("feedChronologicalOrder", $chronologicalOrder);
                $this->yellow->page->setPages("feed", $pages);
                $this->yellow->page->setLastModified($pages->getModified());
            }
        }
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $locationFeed = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("yomliFeedLocation");
            $locationFeed .= $this->yellow->toolbox->normaliseArguments("page:" . $this->yellow->system->get("yomliFeedFileXml"), false);
            $output = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . $this->yellow->system->get("yomliFeedTitle") . "\" href=\"$locationFeed\" />\n";
        }
        return $output;
    }

    // Check if XML requested
    public function isRequestXml($page) {
        return $page->getRequest("page")==$this->yellow->system->get("yomliFeedFileXml");
    }

    //==================================
    // HANDLE CONTACT                 //
    //==================================
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="contact" && ($type=="block" || $type=="inline")) {
            $urlBase = $this->yellow->system->get("coreServerBase") . $this->yellow->system->get("CoreThemeLocation") . "assets/php/email.php";
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<form class=\"contact-form\" id=\"contact-form\" action=\"".$urlBase."\" method=\"post\">\n";
            $output .= "<p class=\"contact-name\"><label for=\"name\">Nom</label><br /><input type=\"text\" class=\"input-alternate\" name=\"name\" id=\"name\" value=\"\" placeholder=\"Spike Spiegel\" /></p>\n";
            $output .= "<p class=\"contact-from\"><label for=\"from\">Email</label><br /><input type=\"email\" class=\"input-alternate\" name=\"from\" id=\"from\" value=\"\" placeholder=\"spike@bebop.jp\" /></p>\n";
            $output .= "<p class=\"contact-message\"><label for=\"message\">Message</label><br /><textarea name=\"message\" id=\"message\" rows=\"7\" cols=\"70\"></textarea></p>\n";
            $output .= "<p class=\"contact-consent\"><input type=\"checkbox\" name=\"consent\" value=\"consent\" id=\"consent\"> <label for=\"consent\">".$this->yellow->language->getTextHtml("contactConsent")."</label></p>\n";
            $output .= "<input type=\"hidden\" name=\"referer\" value=\"".$page->getUrl()."\" />\n";
            $output .= "<input type=\"hidden\" name=\"status\" value=\"send\" />\n";
            $output .= "<!-- Simple honeypot -->\n";
            $output .= "<style>form #website{display:none;}</style>\n";
            $output .= "<input type=\"text\" name=\"website\" id=\"website\" value=\"\" autocomplete=\"off\" />\n";
            $output .= "<input type=\"submit\" value=\"Envoyer le message\" id=\"contact-submit\" />\n";
            $output .= "</form>\n";
            $output .= "</div>\n";
        }
        return $output;
    }
    
    //==================================
    // HANDLE UPDATE                  //
    //==================================
    public function onUpdate($action) {
        $fileName = $this->yellow->system->get("coreExtensionDirectory").$this->yellow->system->get("coreSystemFile");
        if ($action=="install") {
            $this->yellow->system->save($fileName, array("theme" => "yomli"));
        } elseif ($action=="uninstall" && $this->yellow->system->get("theme")=="yomli") {
            $theme = reset(array_diff($this->yellow->system->getValues("theme"), array("yomli")));
            $this->yellow->system->save($fileName, array("theme" => $theme));
        }
    }
}
