// JavaScript Document
function tabPageControl(n)
{ 
for (var i = 0; i < tabTitles.cells.length; i++)
 {
	 tabTitles.cells[i].className = "tabTitleUnSelected";
 }
 tabTitles.cells[n].className = "tabTitleSelected";  
 for (var i = 0; i < tabPagesContainer.tBodies.length; i++)
 {
	 tabPagesContainer.tBodies[i].className = "tabPageUnSelected";
	 }
	 tabPagesContainer.tBodies[n].className = "tabPageSelected";
}