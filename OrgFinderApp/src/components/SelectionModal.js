import React, { useState } from 'react';
import {
    View, Text, TouchableOpacity, Modal,
    StyleSheet, ScrollView, FlatList,
} from 'react-native';

export default function SelectionModal({ visible, title, subtitle, options, selected, max, onConfirm, onCancel }) {
    const [local, setLocal] = useState([...selected]);

    const toggle = (item) => {
        if (local.includes(item)) {
            setLocal(local.filter(i => i !== item));
        } else if (local.length < max) {
            setLocal([...local, item]);
        } else {
            // Already at max — drop the oldest and add the new selection
            setLocal([...local.slice(1), item]);
        }
    };

    const handleConfirm = () => {
        onConfirm(local);
    };

    const handleCancel = () => {
        setLocal([...selected]);
        onCancel();
    };

    return (
        <Modal visible={visible} transparent animationType="slide">
            <View style={styles.overlay}>
                <View style={styles.sheet}>
                    <View style={styles.header}>
                        <View>
                            <Text style={styles.title}>{title}</Text>
                            <Text style={styles.subtitle}>{subtitle}</Text>
                        </View>
                        <Text style={styles.count}>{local.length} selected</Text>
                    </View>

                    <View style={styles.grid}>
                        {options.map((item) => {
                            const active = local.includes(item);
                            return (
                                <TouchableOpacity
                                    key={item}
                                    style={[styles.chip, active && styles.chipActive]}
                                    onPress={() => toggle(item)}
                                >
                                    <Text style={[styles.chipText, active && styles.chipTextActive]}>
                                        {item}
                                    </Text>
                                </TouchableOpacity>
                            );
                        })}
                    </View>

                    <View style={styles.actions}>
                        <TouchableOpacity style={styles.cancelBtn} onPress={handleCancel}>
                            <Text style={styles.cancelText}>Cancel</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={styles.confirmBtn} onPress={handleConfirm}>
                            <Text style={styles.confirmText}>Confirm</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </View>
        </Modal>
    );
}

const styles = StyleSheet.create({
    overlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'flex-end',
    },
    sheet: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 24,
        borderTopRightRadius: 24,
        padding: 24,
        paddingBottom: 40,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: 20,
    },
    title: { fontSize: 18, fontWeight: '700', color: '#1e2f6e' },
    subtitle: { fontSize: 12, color: '#888', marginTop: 2 },
    count: { fontSize: 13, fontWeight: '600', color: '#EF4444' },
    grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 24 },
    chip: {
        paddingHorizontal: 16,
        paddingVertical: 8,
        borderRadius: 20,
        backgroundColor: '#f0f0f0',
        borderWidth: 1,
        borderColor: '#e0e0e0',
    },
    chipActive: {
        backgroundColor: '#4A6CF7',
        borderColor: '#4A6CF7',
    },
    chipText: { fontSize: 13, color: '#333' },
    chipTextActive: { color: '#fff', fontWeight: '600' },
    actions: { flexDirection: 'row', gap: 12 },
    cancelBtn: {
        flex: 1, height: 48, borderRadius: 24,
        borderWidth: 1.5, borderColor: '#ddd',
        alignItems: 'center', justifyContent: 'center',
    },
    cancelText: { fontSize: 15, color: '#555' },
    confirmBtn: {
        flex: 1, height: 48, borderRadius: 24,
        backgroundColor: '#1e3a8a',
        alignItems: 'center', justifyContent: 'center',
    },
    confirmText: { fontSize: 15, color: '#fff', fontWeight: '700' },
});
