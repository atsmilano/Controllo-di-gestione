function submitProcessKey(e, button)
{
    if (null == e)
        e = window.event;
    if (e.keyCode == 13)  {
        document.getElementById(button).click();
        return false;
    }
}
