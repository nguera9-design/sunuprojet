<?php
// fpdf.php - Version simplifiée
define('FPDF_VERSION','1.86');

class FPDF
{
    protected $page;
    protected $n;
    protected $offsets;
    protected $buffer;
    protected $pages;
    protected $state;
    protected $compress;
    protected $k;
    protected $DefOrientation;
    protected $CurOrientation;
    protected $StdPageSizes;
    protected $DefPageSize;
    protected $CurPageSize;
    protected $CurRotation;
    protected $PageInfo;
    protected $wPt, $hPt;
    protected $w, $h;
    protected $lMargin;
    protected $tMargin;
    protected $rMargin;
    protected $bMargin;
    protected $cMargin;
    protected $x, $y;
    protected $lasth;
    protected $LineWidth;
    protected $fontpath;
    protected $CoreFonts;
    protected $fonts;
    protected $FontFiles;
    protected $diffs;
    protected $FontFamily;
    protected $FontStyle;
    protected $underline;
    protected $CurrentFont;
    protected $FontSizePt;
    protected $FontSize;
    protected $DrawColor;
    protected $FillColor;
    protected $TextColor;
    protected $ColorFlag;
    protected $WithRotation;
    protected $angle;
    protected $AliasNbPages;
    protected $ZoomMode;
    protected $LayoutMode;
    protected $metadata;
    protected $PDFVersion;

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->state = 0;
        $this->buffer = '';
        $this->page = 0;
        $this->n = 2;
        $this->offsets = array();
        $this->pages = array();
        $this->PageInfo = array();
        $this->wPt = 0;
        $this->hPt = 0;
        $this->w = 0;
        $this->h = 0;
        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 10;
        $this->cMargin = 1;
        $this->x = 10;
        $this->y = 10;
        $this->lasth = 0;
        $this->LineWidth = 0.2;
        $this->fontpath = '';
        $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
        $this->fonts = array();
        $this->FontFiles = array();
        $this->diffs = array();
        $this->FontFamily = 'helvetica';
        $this->FontStyle = '';
        $this->underline = false;
        $this->CurrentFont = array();
        $this->FontSizePt = 12;
        $this->FontSize = 12;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->WithRotation = false;
        $this->angle = 0;
        $this->AliasNbPages = '{nb}';
        $this->ZoomMode = 'fullpage';
        $this->LayoutMode = 'single';
        $this->metadata = array();
        $this->PDFVersion = '1.3';

        switch($unit)
        {
            case 'mm': $this->k = 72/25.4; break;
            case 'cm': $this->k = 72/2.54; break;
            case 'in': $this->k = 72; break;
            default: $this->k = 1;
        }

        if(is_string($size))
        {
            $this->StdPageSizes = array(
                'a3' => array(841.89,1190.55),
                'a4' => array(595.28,841.89),
                'a5' => array(420.94,595.28),
                'letter' => array(612,792),
                'legal' => array(612,1008)
            );
            $size = strtolower($size);
            if(isset($this->StdPageSizes[$size]))
                $size = $this->StdPageSizes[$size];
            else
                $size = $this->StdPageSizes['a4'];
        }
        if($size[0]>$size[1])
            $size = array($size[1], $size[0]);
        $this->wPt = $size[0];
        $this->hPt = $size[1];
        $this->w = $size[0]/$this->k;
        $this->h = $size[1]/$this->k;

        if($orientation=='P')
        {
            $this->DefOrientation = 'P';
            $this->CurOrientation = 'P';
        }
        else
        {
            $this->DefOrientation = 'L';
            $this->CurOrientation = 'L';
        }
        $this->CurPageSize = $size;
        $this->CurRotation = 0;

        $this->SetMargins(10,10,10);
        $this->SetAutoPageBreak(true,20);
        $this->SetDisplayMode('fullpage');
        $this->SetCompression(true);
        $this->SetTitle('Document');
        $this->SetSubject('');
        $this->SetAuthor('');
        $this->SetKeywords('');
        $this->SetCreator('FPDF');
    }

    function SetMargins($left, $top, $right=null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null) $right = $left;
        $this->rMargin = $right;
    }

    function SetAutoPageBreak($auto, $margin=0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    function SetDisplayMode($zoom, $layout='single')
    {
        $this->ZoomMode = $zoom;
        $this->LayoutMode = $layout;
    }

    function SetCompression($compress)
    {
        $this->compress = $compress;
    }

    function SetTitle($title)
    {
        $this->metadata['Title'] = $title;
    }

    function SetSubject($subject)
    {
        $this->metadata['Subject'] = $subject;
    }

    function SetAuthor($author)
    {
        $this->metadata['Author'] = $author;
    }

    function SetKeywords($keywords)
    {
        $this->metadata['Keywords'] = $keywords;
    }

    function SetCreator($creator)
    {
        $this->metadata['Creator'] = $creator;
    }

    function SetFont($family, $style='', $size=12)
    {
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $this->CurrentFont = array('family'=>$family, 'style'=>$style, 'size'=>$size);
    }

    function SetTextColor($r, $g=null, $b=null)
    {
        if($g===null && $b===null)
            $this->TextColor = sprintf('%.3f g', $r/255);
        else
            $this->TextColor = sprintf('%.3f %.3f %.3f rg', $r/255, $g/255, $b/255);
    }

    function SetFillColor($r, $g=null, $b=null)
    {
        if($g===null && $b===null)
            $this->FillColor = sprintf('%.3f g', $r/255);
        else
            $this->FillColor = sprintf('%.3f %.3f %.3f rg', $r/255, $g/255, $b/255);
    }

    function SetDrawColor($r, $g=null, $b=null)
    {
        if($g===null && $b===null)
            $this->DrawColor = sprintf('%.3f G', $r/255);
        else
            $this->DrawColor = sprintf('%.3f %.3f %.3f RG', $r/255, $g/255, $b/255);
    }

    function SetLineWidth($width)
    {
        $this->LineWidth = $width;
    }

    function AddPage($orientation='', $size='', $rotation=0)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->lasth = 0;
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $k = $this->k;
        if($this->y + $h > $this->PageBreakTrigger && !$this->AutoPageBreak)
            $this->y = $this->PageBreakTrigger;
        if($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        $s = '';
        if($fill || $border==1)
        {
            if($fill)
                $op = ($border==1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        if($txt!=='')
        {
            $this->CurrentFont['size'] = $this->FontSizePt;
            $s .= sprintf('BT %.2f %.2f Td (%s) Tj ET', $this->x*$k, ($this->h-$this->y-0.5*$h)*$k, $this->_escape($txt));
        }
        if($s)
            $this->pages[$this->page] .= $s;
        if($ln)
        {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        }
        else
            $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border=0, $align='', $fill=false)
    {
        $this->Cell($w, $h, $txt, $border, 1, $align, $fill);
    }

    function Ln($h=null)
    {
        if($h===null)
            $h = $this->FontSize;
        $this->y += $h;
    }

    function Line($x1, $y1, $x2, $y2)
    {
        $k = $this->k;
        $s = sprintf('%.2f %.2f m %.2f %.2f l S', $x1*$k, ($this->h-$y1)*$k, $x2*$k, ($this->h-$y2)*$k);
        $this->pages[$this->page] .= $s;
    }

    function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
    {
        // Version simplifiée
    }

    function Output($name='', $dest='')
    {
        if($dest=='I')
        {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="'.$name.'"');
            echo $this->buffer;
        }
        elseif($dest=='D')
        {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$name.'"');
            echo $this->buffer;
        }
        else
        {
            return $this->buffer;
        }
    }

    protected function _escape($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        $s = str_replace("\r", '\\r', $s);
        return $s;
    }
}
?>