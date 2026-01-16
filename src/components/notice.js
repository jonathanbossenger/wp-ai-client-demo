const updateNotice = (message, status = 'info') => {
    const noticeDiv = document.getElementById( 'wp-ai-sdk-demo-notice' );
    noticeDiv.className = `notice notice-${status} is-dismissible`;
    const noticeElement = noticeDiv.querySelector('p');
    noticeElement.innerText = message;
}

const CustomNotice = () => {
    return (
        <div id={"wp-ai-sdk-demo-notice"} className="notice info is-dismissible">
            <p>Ready to generate a post using AI?</p>
        </div>
    )
};

export {updateNotice, CustomNotice};