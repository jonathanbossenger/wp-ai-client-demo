const useInput = () => {
    const [input, setInput] = useState({
        title: "",
        prompt: "",
    });

    const { createSuccessNotice } = useDispatch(noticesStore);

    useEffect(() => {
        apiFetch({ path: "/wp/v2/settings" }).then((wpSettings) => {
            setSettings(wpSettings.unadorned_announcement_bar);
        });
    }, []);

    const saveSettings = () => {
        apiFetch({
            path: "/wp/v2/settings",
            method: "POST",
            data: {
                unadorned_announcement_bar: settings,
            },
        }).then(() => {
            createSuccessNotice(
                __("Settings saved.", "unadorned-announcement-bar"),
            );
        });
    };

    return {
        settings,
        setSettings,
        saveSettings,
    };
};
