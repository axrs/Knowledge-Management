<?php
include('DOMTemplate.class.php');

class Purification
{

    private static $_indentCharacter = '    ';
    public static function Purify($source = '')
    {
        $oldErrorValue = error_reporting();
        error_reporting(0);

        $indentCharacters = self::$_indentCharacter;
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //A list of entities to remain unformatted.
        $keepTagsRegx = '~<script[^>]*>.*?<\/script>|<code[^>]*>.*?<\/code>|<pre[^>]*>.*?<\/pre>|<textarea[^>]*>.*?<\/textarea>~s';
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //Strip all commenting from source, including jquery comments (TURNED OFF AS CODE SNIPPETS ARE USED)
        //When striping contents, ORDER IS VITAL... remove standard html comments before Javascript
        //$source = preg_replace('/<!--[^\[<>].*?(?<!!)-->/m', '', $source); //strip html comments <!-- --> but preserve excessive conditionals
        //$source = preg_replace('%[^-:A-Za-z0-9"]//[^->].*$%m', '', $source); //Strip any single line comments // not starting with a character or ending in ->
        //$source = preg_replace('!/\*.*?\*/!s', '', $source); //strip /* */ comments
        //Source Cleaning
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //Microsoft Word Input Cleaning
        $source = ereg_replace("<(/)?(font|span|del|ins)[^>]*>","",$source);
        $source = ereg_replace("<([^>]*)(lang|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$source);
        $source = ereg_replace("<([^>]*)(lang|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$source);
        //Additional House Cleaning

        $source = str_replace("-&gt;", "&rarr;", $source); //Replace -> with html encoded right arrow
        $source = str_replace("→", "&rarr;", $source); //Replace -> with html encoded right arrow
        $source = str_replace(" :)", " &#9786;", $source); //Replace -> with html encoded right arrow
        $source = str_replace("☺", "&#9786;", $source); //Replace -> with html encoded right arrow



        //Article Linking. Replace {{##}} with article link
        while(preg_match('/\{\{([0-9^}]*)\}\}/i', $source, $regs)){
            $article = new KnowledgeManager($regs[1]);
            $url = $article->isLoaded() ? $article->getLink() : '';
            $source = preg_replace('/\{\{([0-9^}]*)\}\}/i',$url,$source,1);
        }

        $source = preg_replace('~\r\n~ms', "\n", $source); // replace \r\n with \n
        $source = preg_replace('~\r~ms', "\n", $source); // replace \r with \n
        $source = preg_replace('~^\s+~s', '', $source); // remove whitespace from the start
        $source = preg_replace('~\s+$~s', '', $source); // remove whitespace from the end
        preg_match_all($keepTagsRegx, $source, $original_tags); // store all tags which should  be preserved
        $source = preg_replace('~^\s+~m', '', $source); // remove whitespace from the beginning of each line
        $source = preg_replace('~\s+$~m', '', $source); // remove whitespace from the end of each line
        $source = preg_replace('~([^>\s])(\s\s+|\n)([^<\s])~m', '$1 $3', $source); // removes line breaks inside normal text
        $source = preg_replace(array('~([.,!?])(\\1+)~', '~[?!]{2,}~'), array('\\1', '?'), $source); //Duplicate punctuation
        //$source = preg_replace('#([?!.-])\1{2,}#', '$1$1', $source);
        //$source = preg_replace('#([?!.-][?!.-]+?)\1+#', '$1', $source);
        $source = preg_replace("/\s(\w+\s)\1/i", "$1", $source); // remove duplicate words
        $source = preg_replace("/[^[:print:]]+/", "", $source); //Remove non printable characters
        $source = str_replace(array("  ", "  ", "  ", "  ", "  "), ' ', $source); //Remove double spaces
        $source = preg_replace("/<p[^>]*><\\/p[^>]*>/", "", $source); //Remove empty paragraph tags
        $source = preg_replace("/<p[^>]*><br><\\/p[^>]*>/", "", $source); //Remove empty break paragraph tags
        $source = str_replace("&nbsp;", '', $source); //Remove double breaks

        $source = str_replace(array("<br><br>", "<br><br>", "<br><br>"), '<br>', $source); //Remove double breaks
        $source = preg_replace('%^(<span(.*?)style="font-weight:bold;"(.*?)>(.*?)</span>)$%','<strong>$4</strong>',$source);//Span to strong
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // Remove any Empty lines
        $source = preg_replace('~\n\s*(?=\n)~ms', '', $source);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // Adjust the indentation of the document
        $indent = 0;
        $source = explode("\n", $source);
        foreach ($source as &$line) {
            $correction = intval(substr($line, 0, 2) == '</'); // correct indention, if line starts with closing tag
            if ( $indentCharacters == '\t') $indentCharacters = "\t";
            $line = str_repeat($indentCharacters, $indent - $correction) . $line;
            $indent += substr_count($line, '<'); // indent every tag
            $indent -= substr_count($line, '<!'); // subtract doctype declaration
            $indent -= substr_count($line, '<?'); // subtract processing instructions
            $indent -= substr_count($line, '/>'); // subtract self closing tags
            $indent -= substr_count($line, '</') * 2; // subtract closing tags
        }
        $source = implode("\n", $source);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        preg_match_all($keepTagsRegx, $source, $current_tags); // fetch any tags that were preserved
        foreach ($current_tags[0] as $key => $match) {
            $source = str_replace($match, $original_tags[0][$key], $source);
        } // restore all stored tags

        $doc = new DOMDocument();

        $doc->strictErrorChecking = false;
        $doc->substituteEntities = false;
        (true);
        @$doc->loadHTML($source);

        $x = new DOMXPath($doc);

        foreach($x->query("//pre") as $node)
        {
            $node->setAttribute("class","code");
        }

        $source = $doc->saveHTML();
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $source, $matches);
        error_reporting($oldErrorValue);
        return $matches[1];
    }
}