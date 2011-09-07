<?PHP
//=== new pager... count is total number, perpage is duh!, url is whatever it's goint too \o <=== and that's me waving to pdq, just saying "hi there"
	function pager_new($count, $perpage, $page, $url, $page_link = false) 
	{
	$pages = floor($count / $perpage);
		if ($pages * $perpage < $count)
  		++$pages;

			//=== lets make php happy :P
			$page_num = '';
			$page = ($page < 1 ? 1 : $page);
			$page = ($page > $pages ? $pages : $page);
			
			//=== lets add the ... if too many pages
			switch (true)
			{
			case ($pages < 11):
					for ($i = 1; $i <= $pages; ++$i)
					{
					$page_num .= ($i == $page ? ' <b>'.$i.'</b> ' : ' <a class="altlink" href="'.$url.'&amp;page='.$i.$page_link.'"><b>'.$i.'</b></a> ');
					}
			break;
			case ($pages > 11):
					for ($i = 1; $i < 5; ++$i)
           	 			{
   	         			$page_num .= ($i == $page ? ' <b>'.$i.'</b> ' : ' <a class="altlink" href="'.$url.'&amp;page='.$i.$page_link.'"><b>'.$i.'</b></a> ');
						}
					$page_num .= ' ... ';
					for ($i = ($pages - 3); $i <= $pages; ++$i)
						{
						$page_num .= ($i == $page ? ' <b>'.$i.'</b> ' : ' <a class="altlink" href="'.$url.'&amp;page='.$i.$page_link.'"><b>'.$i.'</b></a> ');
						}
			break;
			}
					
				$menu = ($page == 1? ' <p align="center"><b><img src="pic/arrow_prev.gif" alt="&lt;&lt;" title="&lt;&lt;" /> Prev</b> ' : ' <p align="center"><a class="altlink" href="'.$url.'&amp;page='.($page - 1).$page_link.'"><b><img src="pic/arrow_prev.gif" alt="&lt;&lt;" title="&lt;&lt;" /> Prev</b></a>').'&nbsp;&nbsp;&nbsp;'.$page_num.'&nbsp;&nbsp;&nbsp;'.($page == $pages ? '<b>Next <img src="pic/arrow_next.gif" alt="&gt;&gt;" title="&gt;&gt;" /></b> ' : ' <a class="altlink" href="'.$url.'&amp;page='.($page + 1).$page_link.'"><b>Next <img src="pic/arrow_next.gif" alt="&gt;&gt;" title="&gt;&gt;" /></b></a></p>');
				$offset = ($page * $perpage) - $perpage;

			$LIMIT =  ($count > 0 ? "LIMIT $offset,$perpage" : '');

		return array($menu, $LIMIT);
	} //=== end pager function
?>